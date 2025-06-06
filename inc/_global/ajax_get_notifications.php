<?php
// /inc/_global/ajax_get_notifications.php
require __DIR__ . '/config.php';
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

// 2a) Fetch unread count
$countStmt = $pdo->prepare("
    SELECT COUNT(*) AS cnt
    FROM `demon_notifications`
    WHERE user_id = :uid
      AND is_read = 0
");
$countStmt->execute(['uid' => $currentUserId]);
$row = $countStmt->fetch();
$unreadCount = (int)$row['cnt'];

// 2b) Fetch latest 5 notifications (id, message, link, is_read, created_at)
$notifStmt = $pdo->prepare("
    SELECT id, message, link, is_read, created_at
    FROM `demon_notifications`
    WHERE user_id = :uid
    ORDER BY created_at DESC
    LIMIT 5
");
$notifStmt->execute(['uid' => $currentUserId]);
$notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);

// 3) Return JSON
echo json_encode([
    'success'       => true,
    'unread_count'  => $unreadCount,
    'notifications' => $notifications
]);
exit;
