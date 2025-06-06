<?php
// inc/_global/ajax_get_balance.php

require __DIR__ . '/config.php';
session_start();
header('Content-Type: application/json');

$currentUserId = (int)($_SESSION['user_id'] ?? 0);
if ($currentUserId === 0) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit;
}

$balStmt = $pdo->prepare("
    SELECT credit_balance
    FROM `demon_users`
    WHERE id = :uid
    LIMIT 1
");
$balStmt->execute(['uid' => $currentUserId]);
$balance = (int)$balStmt->fetchColumn();

echo json_encode([
    'success' => true,
    'balance' => $balance
]);
exit;
