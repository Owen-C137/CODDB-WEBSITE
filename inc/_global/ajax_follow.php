<?php
// inc/_global/ajax_follow.php

require __DIR__ . '/config.php';          // loads $pdo, $site, etc.
require __DIR__ . '/login_check.php';     // ensures session_start() and $_SESSION['user_id']
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$viewUserId    = isset($_POST['uid']) ? (int)$_POST['uid'] : 0;
$action        = $_POST['action'] ?? '';
$csrfToken     = $_POST['csrf_token'] ?? '';

// Basic validation
if (!$currentUserId || !$viewUserId || !in_array($action, ['follow', 'unfollow'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid parameters.']);
    exit;
}

// You can’t follow/unfollow yourself
if ($currentUserId === $viewUserId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cannot follow/unfollow yourself.']);
    exit;
}

// CSRF check
if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token.']);
    exit;
}

try {
    // Fetch the current user's username for use in notifications
    $userStmt = $pdo->prepare("SELECT username FROM demon_users WHERE id = :uid LIMIT 1");
    $userStmt->execute([':uid' => $currentUserId]);
    $row = $userStmt->fetch(PDO::FETCH_ASSOC);
    $followerUsername = $row['username'] ?? 'Someone';

    if ($action === 'follow') {
        // 1) Insert follow row
        $ins = $pdo->prepare("
            INSERT IGNORE INTO demon_follows (user_id, follower_id)
            VALUES (:uid, :fid)
        ");
        $ins->execute([
            'uid' => $viewUserId,
            'fid' => $currentUserId
        ]);

        // 2) If insert actually affected something (new follow), create a notification
        if ($ins->rowCount() > 0) {
            $message = "{$followerUsername} started following you.";
            $link    = "profile.php?uid={$currentUserId}";

            $notifStmt = $pdo->prepare("
                INSERT INTO demon_notifications 
                  (user_id, message, link, is_read, created_at)
                VALUES
                  (:followedUserId, :message, :link, 0, NOW())
            ");
            $notifStmt->execute([
                'followedUserId' => $viewUserId,
                'message'        => $message,
                'link'           => $link
            ]);
        }

    } else { // action === 'unfollow'
        // 1) Delete follow row
        $del = $pdo->prepare("
            DELETE FROM demon_follows
            WHERE user_id = :uid AND follower_id = :fid
        ");
        $del->execute([
            'uid' => $viewUserId,
            'fid' => $currentUserId
        ]);

        // 2) If a row was deleted, send an unfollow notification
        if ($del->rowCount() > 0) {
            $message = "{$followerUsername} unfollowed you.";
            $link    = "profile.php?uid={$currentUserId}";

            $notifStmt = $pdo->prepare("
                INSERT INTO demon_notifications
                  (user_id, message, link, is_read, created_at)
                VALUES
                  (:unfollowedUserId, :message, :link, 0, NOW())
            ");
            $notifStmt->execute([
                'unfollowedUserId' => $viewUserId,
                'message'          => $message,
                'link'             => $link
            ]);
        }
    }

    // ────────────────────────────────────────────────
    //  After any follow/unfollow, immediately re‐run
    //  our two quest‐helpers, so that users can “complete”
    //  follow‐related quests in real‐time.
    //  (They’ll insert into demon_user_quests and award credits.)
    //
    //  Paths are relative to this file: inc/_global/ajax_follow.php
    // ────────────────────────────────────────────────

    require_once __DIR__ . '/quests/follow_5_users.php';
    require_once __DIR__ . '/quests/be_followed_10.php';

    // ────────────────────────────────────────────────

    // Fetch updated follower count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM demon_follows WHERE user_id = :uid");
    $countStmt->execute(['uid' => $viewUserId]);
    $followersCount = (int)$countStmt->fetchColumn();

    // Determine new isFollowing state
    $chkStmt = $pdo->prepare("
      SELECT 1 FROM demon_follows
      WHERE user_id = :view AND follower_id = :cur
      LIMIT 1
    ");
    $chkStmt->execute([
      'view'=> $viewUserId,
      'cur' => $currentUserId
    ]);
    $isFollowing = (bool)$chkStmt->fetchColumn();

    echo json_encode([
        'success'        => true,
        'followersCount' => $followersCount,
        'isFollowing'    => $isFollowing
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
    exit;
}
