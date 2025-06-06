<?php
// inc/_global/ajax_profile_react.php

require __DIR__ . '/../_global/config.php';
session_start();

header('Content-Type: application/json');

// 1) Must be logged in
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
if ($currentUserId === 0) {
    echo json_encode([
        'success' => false,
        'error'   => 'You must be logged in to react.'
    ]);
    exit;
}

// 2) CSRF token check
$csrfToken = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid CSRF token.'
    ]);
    exit;
}

// 3) Gather inputs
$postId   = (int)($_POST['post_id'] ?? 0);
$react    = ($_POST['react'] ?? ''); // expected: 'like','love','laugh','angry'
$validReacts = ['like','love','laugh','angry'];

if ($postId <= 0 || !in_array($react, $validReacts, true)) {
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid post ID or reaction type.'
    ]);
    exit;
}

// 4) Fetch post owner
$fetchOwner = $pdo->prepare("
  SELECT user_id
  FROM `demon_user_profile_posts`
  WHERE id = :pid
  LIMIT 1
");
$fetchOwner->execute(['pid' => $postId]);
$postRow = $fetchOwner->fetch(PDO::FETCH_ASSOC);

if (!$postRow) {
    echo json_encode([
        'success' => false,
        'error'   => 'Post not found.'
    ]);
    exit;
}

$postOwnerId = (int)$postRow['user_id'];

// 5) Check if the current user already has a reaction on this post
$fetchReact = $pdo->prepare("
  SELECT id, react_type
  FROM `demon_user_profile_reacts`
  WHERE post_id = :pid AND user_id = :uid
  LIMIT 1
");
$fetchReact->execute([
    'pid' => $postId,
    'uid' => $currentUserId
]);
$existing = $fetchReact->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    // a) If same reaction type => remove it (toggle off)
    if ($existing['react_type'] === $react) {
        $del = $pdo->prepare("
          DELETE FROM `demon_user_profile_reacts`
          WHERE id = :rid
        ");
        $del->execute(['rid' => $existing['id']]);
        $actionTaken = 'removed';
    } else {
        // b) If different type => update it
        $upd = $pdo->prepare("
          UPDATE `demon_user_profile_reacts`
          SET react_type = :react
          WHERE id = :rid
        ");
        $upd->execute([
            'react' => $react,
            'rid'   => $existing['id']
        ]);
        $actionTaken = 'updated';
    }
} else {
    // c) No existing reaction => insert new
    $ins = $pdo->prepare("
      INSERT INTO `demon_user_profile_reacts`
        (post_id, user_id, react_type)
      VALUES
        (:pid, :uid, :react)
    ");
    $ins->execute([
        'pid'   => $postId,
        'uid'   => $currentUserId,
        'react' => $react
    ]);
    $actionTaken = 'added';
}

// 6) Re‐count all reacts per type for this post
$counts = [];
foreach ($validReacts as $type) {
    $cntStmt = $pdo->prepare("
      SELECT COUNT(*) 
      FROM `demon_user_profile_reacts`
      WHERE post_id = :pid AND react_type = :type
    ");
    $cntStmt->execute([
        'pid'  => $postId,
        'type' => $type
    ]);
    $counts[$type] = (int)$cntStmt->fetchColumn();
}

// 7) If a reaction was “added” (not if simply removed), send a notification
if (in_array($actionTaken, ['added','updated'], true) && $postOwnerId !== $currentUserId) {
    // Build message based on reaction type
    switch ($react) {
        case 'like':
            $msgText = htmlspecialchars($_SESSION['username']) . ' liked your post.';
            break;
        case 'love':
            $msgText = htmlspecialchars($_SESSION['username']) . ' loved your post.';
            break;
        case 'laugh':
            $msgText = htmlspecialchars($_SESSION['username']) . ' found your post funny.';
            break;
        case 'angry':
            $msgText = htmlspecialchars($_SESSION['username']) . ' did not like your post.';
            break;
        default:
            $msgText = htmlspecialchars($_SESSION['username']) . ' reacted to your post.';
    }

    // Link to the post anchor on the user’s profile
    $notifLink = "/profile.php?uid={$postOwnerId}#post-{$postId}";

    $insNotif = $pdo->prepare("
      INSERT INTO `demon_notifications`
        (user_id, message, link)
      VALUES
        (:uid, :msg, :link)
    ");
    $insNotif->execute([
        'uid'  => $postOwnerId,
        'msg'  => $msgText,
        'link' => $notifLink
    ]);
}

// 8) Return JSON with updated counts
echo json_encode([
    'success'     => true,
    'counts'      => $counts,
    'actionTaken' => $actionTaken
]);
exit;
