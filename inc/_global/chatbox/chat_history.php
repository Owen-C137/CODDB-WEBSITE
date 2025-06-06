<?php
// chat_history.php
require __DIR__ . '/../config.php';
session_start();

header('Content-Type: application/json');
if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Expect `before_id` (fetch messages older than that ID), and `limit` (max number to load)
$beforeId = isset($_GET['before_id']) ? (int)$_GET['before_id'] : 0;
$limit    = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
if ($limit <= 0 || $limit > 200) $limit = 50;

// 1) If before_id = 0, treat as “fetch latest” → same as chat_fetch, but no pinned
if ($beforeId <= 0) {
    echo json_encode(['error' => 'Invalid before_id']);
    exit;
}

// 2) Fetch messages with id < before_id, ordered by created_at DESC limit $limit
$stmt = $pdo->prepare("
    SELECT m.id, m.user_id, m.content, m.created_at, u.username
    FROM demon_messages m
    JOIN demon_users u ON u.id = m.user_id
    WHERE m.id < :before_id
      AND m.is_pinned = 0
    ORDER BY m.created_at DESC
    LIMIT :limit
");
$stmt->bindValue(':before_id', $beforeId, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Reverse → oldest→newest
$rows = array_reverse($rows);

// Return JSON
echo json_encode(['messages' => $rows]);
exit;
