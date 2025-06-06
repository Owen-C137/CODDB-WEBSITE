<?php
// logout.php

require 'inc/_global/config.php';

session_start();

// If you don’t already have a DB‐connection check at top of config.php, add one:
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die('Cannot connect to database.');
}

// Helper: insert an audit log entry, but don’t let it break logout if it fails
function insert_audit_log(PDO $pdo, ?int $userId, string $action, array $detailsArray, string $ip): void {
    $sql = "
        INSERT INTO `demon_audit_logs`
          (user_id, action, details, ip_address, created_at)
        VALUES
          (:uid, :action, :details, :ip, NOW())
    ";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'uid'     => $userId,
            'action'  => $action,
            'details' => json_encode($detailsArray),
            'ip'      => $ip,
        ]);
    } catch (PDOException $e) {
        error_log('Audit‐log failed: ' . $e->getMessage());
    }
}

// Destroy PHP session and cookie in one helper
function destroy_session_and_cookie(): void {
    // 1) Unset all session variables
    $_SESSION = [];

    // 2) Delete the PHPSESSID cookie if it’s in use
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        // On PHP ≥ 7.3, you could add 'samesite' => 'Lax' in the options array
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // 3) Destroy the session
    session_destroy();
}

// === Determine current user and IP for logging ===
$currentUserId = $_SESSION['user_id'] ?? null;
$ipAddressRaw  = $_SERVER['REMOTE_ADDR'] ?? '';
$ipAddress = filter_var($ipAddressRaw, FILTER_VALIDATE_IP) 
    ? $ipAddressRaw 
    : '0.0.0.0';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

// === 1) If there is a remember‐me cookie, delete it from the database ===
if (!empty($_COOKIE['remember_token'])) {
    $rawToken = trim($_COOKIE['remember_token']);

    // Validate format (e.g. hex, length 64)? Adjust regex if your token format is different
    if (preg_match('/^[0-9a-f]{64}$/i', $rawToken)) {
        // Remove this token from demon_user_sessions
        $delStmt = $pdo->prepare("
            DELETE FROM `demon_user_sessions`
            WHERE session_token = :token
        ");
        $delStmt->execute(['token' => $rawToken]);
    }

    // Clear the cookie with SameSite if PHP ≥ 7.3; otherwise, fall back to basic signature
    if (PHP_VERSION_ID >= 70300) {
        setcookie('remember_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => $_SERVER['HTTP_HOST'] ?? '',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } else {
        setcookie(
            'remember_token',
            '',
            time() - 3600,
            '/',
            $_SERVER['HTTP_HOST'] ?? '',
            true,
            true
        );
    }
}

// === Audit log: user logout ===
insert_audit_log(
    $pdo,
    $currentUserId,
    'user.logout',
    [
        'ip'    => $ipAddress,
        'agent' => $userAgent,
    ],
    $ipAddress
);

// === 2) Destroy the entire session ===
destroy_session_and_cookie();

// === 3) Redirect back to login page ===
// No output must be sent before this
header('Location: login.php');
exit;
