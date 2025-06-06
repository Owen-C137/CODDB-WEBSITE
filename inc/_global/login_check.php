<?php
// inc/_global/login_check.php
// -------------------------------------------------
// Just enforce “must be logged in” (or via remember‐me).
// No credit‐balance logic in here.
// -------------------------------------------------

// If $pdo isn’t already set, load our global config
if (!isset($pdo)) {
    require_once __DIR__ . '/config.php';
}

session_start();

// If session already has user_id, we’re done
if (isset($_SESSION['user_id'])) {
    return;
}

// Otherwise, try “remember me” cookie logic
if (!empty($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    $sql  = "
      SELECT user_id, expires_at
      FROM `demon_user_sessions`
      WHERE session_token = :token
        AND session_type    = 'remember_me'
      LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':token' => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && strtotime($row['expires_at']) > time()) {
        // valid remember‐me → re‐hydrate session
        $_SESSION['user_id'] = (int)$row['user_id'];
        return;
    }

    // invalid/expired → wipe cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// If we’ve reached here, no valid session = redirect
header('Location: /logout.php');
exit;
