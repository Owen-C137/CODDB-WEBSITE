<?php
// inc/_global/ajax_whos_online.php

require __DIR__ . '/../_global/config.php';
session_start();

header('Content-Type: application/json');

// 1) Must be logged in
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
if ($currentUserId === 0) {
    echo json_encode([
        'success' => false,
        'error'   => 'Not authenticated'
    ]);
    exit;
}

// 2) Update this user’s last_activity = NOW()
$upd = $pdo->prepare("
    UPDATE `demon_users`
    SET last_activity = NOW()
    WHERE id = :uid
");
$upd->execute(['uid' => $currentUserId]);

// 3) Fetch everyone who’s been active in the last 2 minutes and is_active = 1
$stmt = $pdo->query("
    SELECT username
    FROM `demon_users`
    WHERE last_activity >= (NOW() - INTERVAL 2 MINUTE)
      AND is_active = 1
    ORDER BY username ASC
");
$onlineUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 4) Return JSON (even if zero users)
echo json_encode([
    'success'     => true,
    'users'       => $onlineUsers
]);
exit;
