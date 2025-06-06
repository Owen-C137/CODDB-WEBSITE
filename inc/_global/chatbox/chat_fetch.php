<?php
// chat_fetch.php
require __DIR__ . '/../config.php';
session_start();
header('Content-Type: application/json');

// 1) Check user is logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}
$userId = (int)$_SESSION['user_id'];

// 2) Determine how many to fetch. Default = 50.
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
if ($limit <= 0 || $limit > 200) {
    $limit = 50;
}

// 3) First fetch pinned messages (is_pinned = 1), ordered oldest→newest
$pinnedStmt = $pdo->prepare("
    SELECT 
      m.id,
      m.user_id,
      m.content,
      m.created_at,
      u.username,
      -- Check profiles first, then users, then default
      COALESCE(
        up.profile_picture_url, 
        u.profile_picture_url, 
        '/assets/media/avatars/avatar_placeholder.jpg'
      ) AS avatar_url
    FROM demon_messages m
    JOIN demon_users u ON u.id = m.user_id
    LEFT JOIN demon_user_profiles up ON up.user_id = u.id
    WHERE m.is_pinned = 1
    ORDER BY m.created_at ASC
");
$pinnedStmt->execute();
$pinned = $pinnedStmt->fetchAll(PDO::FETCH_ASSOC);

// 4) Then fetch the most recent `limit` non-pinned messages
$nonPinnedStmt = $pdo->prepare("
    SELECT 
      m.id,
      m.user_id,
      m.content,
      m.created_at,
      u.username,
      COALESCE(
        up.profile_picture_url, 
        u.profile_picture_url, 
        '/assets/media/avatars/avatar_placeholder.jpg'
      ) AS avatar_url
    FROM demon_messages m
    JOIN demon_users u ON u.id = m.user_id
    LEFT JOIN demon_user_profiles up ON up.user_id = u.id
    WHERE m.is_pinned = 0
    ORDER BY m.created_at DESC
    LIMIT :limit
");
$nonPinnedStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$nonPinnedStmt->execute();
$nonPinned = $nonPinnedStmt->fetchAll(PDO::FETCH_ASSOC);

// 5) Reverse the non-pinned array so it’s oldest→newest
$nonPinned = array_reverse($nonPinned);

// 6) Mark which messages the current user has reacted to
$messageIds = array_merge(
    array_column($pinned, 'id'),
    array_column($nonPinned, 'id')
);
if (count($messageIds)) {
    $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
    $reactStmt = $pdo->prepare("
        SELECT message_id, reaction
        FROM demon_message_reactions
        WHERE user_id = ?
          AND message_id IN ($placeholders)
    ");
    $reactStmt->execute(array_merge([$userId], $messageIds));
    $userReacts = $reactStmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);
} else {
    $userReacts = [];
}

// 7) Build final array
$response = [
    'pinned'      => $pinned,
    'messages'    => $nonPinned,
    'userReacted' => $userReacts, // e.g. ['42' => ['👍','❤️'], '57' => ['😂']]
];

// 8) Return JSON
echo json_encode($response);
exit;
