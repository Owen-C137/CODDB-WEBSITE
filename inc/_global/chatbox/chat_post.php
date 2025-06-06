<?php
// /inc/_global/chatbox/chat_post.php

require __DIR__ . '/../config.php';
session_start();

// Always return JSON
header('Content-Type: application/json');

// 1) Check that user is logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}
$userId = (int)$_SESSION['user_id'];

// 2) Validate CSRF: compare against chat-specific token
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['chat_csrf_token']) ||
    !hash_equals($_SESSION['chat_csrf_token'], $_POST['csrf_token'])
) {
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// 3) Get and validate message content (max 200 chars)
$raw = trim((string)($_POST['content'] ?? ''));
$maxLen = 200;
if (mb_strlen($raw) === 0 || mb_strlen($raw) > $maxLen) {
    echo json_encode(['error' => "Message must be between 1 and $maxLen characters."]);
    exit;
}

// 4) Simple sanitization (strip scripts, escape HTML)
function sanitize_message(string $msg): string {
    // Remove any <script>...</script>
    $msg = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $msg);
    // Escape HTML entities
    return htmlspecialchars($msg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
$sanitized = sanitize_message($raw);

// 5) Insert into demon_messages
try {
    $stmt = $pdo->prepare("
        INSERT INTO demon_messages (user_id, content, created_at)
        VALUES (:uid, :content, NOW())
    ");
    $stmt->execute([
        'uid'     => $userId,
        'content' => $sanitized
    ]);
    $newId = (int)$pdo->lastInsertId();
} catch (Exception $e) {
    // Log and return error
    error_log("Chat insert failed: " . $e->getMessage());
    echo json_encode(['error' => 'Unable to post message.']);
    exit;
}

// 6) Fetch the newly inserted row (including username + avatar_url)
$fetch = $pdo->prepare("
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
    WHERE m.id = :mid
    LIMIT 1
");
$fetch->execute(['mid' => $newId]);
$row = $fetch->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(['error' => 'Could not retrieve new message.']);
    exit;
}

// 7) Bump “Chat 50 Times” quest progress
require_once __DIR__ . '/../quests/chat_50_times.php';
recordChat50Progress($pdo, $userId);

// 8) Return JSON for the client to append
echo json_encode([
    'message' => $row
]);
exit;
