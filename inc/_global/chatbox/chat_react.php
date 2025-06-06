<?php
// chat_react.php
require __DIR__ . '/../config.php';
session_start();

header('Content-Type: application/json');

// 1) Check login
if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}
$userId = (int)$_SESSION['user_id'];

// 2) Get POST data
$messageId = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
$reaction  = trim($_POST['reaction'] ?? '');   // e.g. "👍"
$action    = ($_POST['action'] === 'remove') ? 'remove' : 'add';

if ($messageId <= 0 || $reaction === '') {
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

// 3) If action = add, insert row if it doesn’t exist. If remove, delete it.
if ($action === 'add') {
    try {
        $ins = $pdo->prepare("
            INSERT IGNORE INTO demon_message_reactions
            (message_id, user_id, reaction, created_at)
            VALUES (:mid, :uid, :react, NOW())
        ");
        $ins->execute([
            'mid'   => $messageId,
            'uid'   => $userId,
            'react' => $reaction
        ]);
        // You might also want to return the new total count for this reaction on that message.
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) AS cnt
            FROM demon_message_reactions
            WHERE message_id = :mid
              AND reaction = :react
        ");
        $countStmt->execute(['mid' => $messageId, 'react' => $reaction]);
        $cnt = (int)$countStmt->fetchColumn();
        echo json_encode([
            'message'     => 'added',
            'reaction'    => $reaction,
            'count'       => $cnt
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['error' => 'Could not add reaction.']);
        exit;
    }
} else {
    // remove
    $del = $pdo->prepare("
        DELETE FROM demon_message_reactions
        WHERE message_id = :mid
          AND user_id = :uid
          AND reaction = :react
    ");
    $del->execute([
        'mid'   => $messageId,
        'uid'   => $userId,
        'react' => $reaction
    ]);
    // Return new count
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) AS cnt
        FROM demon_message_reactions
        WHERE message_id = :mid
          AND reaction = :react
    ");
    $countStmt->execute(['mid' => $messageId, 'react' => $reaction]);
    $cnt = (int)$countStmt->fetchColumn();
    echo json_encode([
        'message'     => 'removed',
        'reaction'    => $reaction,
        'count'       => $cnt
    ]);
    exit;
}
