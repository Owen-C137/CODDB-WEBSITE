<?php
// chat_typing.php
require __DIR__ . '/../config.php';
session_start();

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}
$userId = (int)$_SESSION['user_id'];

// 1) Upsert into demon_typing_status
try {
    $stmt = $pdo->prepare("
        INSERT INTO demon_typing_status (user_id, last_typing_at)
        VALUES (:uid, NOW())
        ON DUPLICATE KEY UPDATE last_typing_at = NOW()
    ");
    $stmt->execute(['uid' => $userId]);
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Could not update typing status.']);
}
exit;
