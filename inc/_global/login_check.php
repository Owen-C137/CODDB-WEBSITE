<?php
// inc/_global/login_check.php
// -------------------------------------------------
// Enforce login + session timeout + remember-me.
// -------------------------------------------------

// Ensure we have our DB & config
require_once __DIR__ . '/config.php';

// Start buffering and session
session_start();

// ────────────────
// 1) Session Timeout
// ────────────────
$timeoutSeconds = 30 * 60; // e.g. 30 minutes
if (isset($_SESSION['LAST_ACTIVITY'])
    && (time() - $_SESSION['LAST_ACTIVITY'] > $timeoutSeconds)
) {
    // Timeout hit: clear session and force logout
    session_unset();
    session_destroy();
    header('Location: /logout.php');
    exit;
}
// Update last‐activity timestamp
$_SESSION['LAST_ACTIVITY'] = time();

// ────────────────
// 2) Already logged in?
// ────────────────
if (isset($_SESSION['user_id'])) {
    // You’re good—no further action needed
    return;
}

// ────────────────────────
// 3) “Remember Me” Cookie
//────────────────────────
if (!empty($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $sql   = "
      SELECT user_id, expires_at
      FROM demon_user_sessions
      WHERE session_token = :token
        AND session_type    = 'remember_me'
      LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':token' => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && strtotime($row['expires_at']) > time()) {
        // Valid token → restore session
        $userId = (int)$row['user_id'];
        $_SESSION['user_id'] = $userId;

        // Also fetch username & role for header display
        $stmt2 = $pdo->prepare("
          SELECT username, role_id
          FROM demon_users
          WHERE id = :uid
          LIMIT 1
        ");
        $stmt2->execute([':uid' => $userId]);
        if ($u = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['username'] = $u['username'];
            $_SESSION['role_id']  = $u['role_id'];
        }
        return;
    }

    // Token expired or invalid → clear cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// ─────────────────────────────
// 4) Nothing worked → Log out
//─────────────────────────────
header('Location: /logout.php');
exit;
