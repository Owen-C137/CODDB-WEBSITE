<?php
// inc/_global/ajax_profile_post.php

require __DIR__ . '/../_global/config.php';
session_start();

header('Content-Type: application/json');

// 1) Must be logged in
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
if ($currentUserId === 0) {
    echo json_encode([
        'success' => false,
        'error'   => 'You must be logged in to do that.'
    ]);
    exit;
}

// 2) Determine action (now accepting "update" instead of "edit")
$action = $_POST['action'] ?? '';
if (! in_array($action, ['create','update','delete'], true)) {
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid action.'
    ]);
    exit;
}

// 3) CSRF token check
$csrfToken = $_POST['csrf_token'] ?? '';
if (! hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid CSRF token.'
    ]);
    exit;
}

// 4) “Create” a new profile post
if ($action === 'create') {
    $title   = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($title === '' || $message === '') {
        echo json_encode([
            'success' => false,
            'error'   => 'Title and message cannot be empty.'
        ]);
        exit;
    }

    // Insert into database
    $ins = $pdo->prepare("
      INSERT INTO `demon_user_profile_posts`
        (user_id, title, message)
      VALUES
        (:uid, :title, :msg)
    ");
    $ins->execute([
        'uid'   => $currentUserId,
        'title' => $title,
        'msg'   => $message
    ]);
    $newPostId = (int)$pdo->lastInsertId();

    // Fetch the newly inserted post (so we can render it back)
    $fetch = $pdo->prepare("
      SELECT id, user_id, title, message, created_at, updated_at
      FROM `demon_user_profile_posts`
      WHERE id = :pid
      LIMIT 1
    ");
    $fetch->execute(['pid' => $newPostId]);
    $row = $fetch->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode([
            'success' => false,
            'error'   => 'Failed to retrieve the new post.'
        ]);
        exit;
    }

    //
    // ────────────────────────────────────────────────────────────────────────
    // 2a) SEND “NEW PROFILE POST” NOTIFICATIONS TO EVERY FOLLOWER
    // ────────────────────────────────────────────────────────────────────────
    //
    // Fetch the username of the poster (we’ll need it for the message)
    $posterUsername = htmlspecialchars($_SESSION['username']);

    // Build the link pointing to the new post on their profile
    $notifLink = "/profile.php?uid={$currentUserId}#post-{$newPostId}";

    // 1) Get a list of everyone who follows this user
    $getFollowers = $pdo->prepare("
      SELECT follower_id
      FROM `demon_follows`
      WHERE user_id = :poster
    ");
    $getFollowers->execute(['poster' => $currentUserId]);

    // 2) For each follower, insert a notification record
    while ($f = $getFollowers->fetch(PDO::FETCH_ASSOC)) {
        $followerId = (int)$f['follower_id'];
        // Don’t notify yourself if somehow you follow yourself
        if ($followerId === $currentUserId) {
            continue;
        }
        $messageText = "{$posterUsername} has posted on their profile.";
        $insNotif = $pdo->prepare("
          INSERT INTO `demon_notifications`
            (user_id, message, link)
          VALUES
            (:uid, :msg, :link)
        ");
        $insNotif->execute([
            'uid'  => $followerId,
            'msg'  => $messageText,
            'link' => $notifLink
        ]);
    }
    //
    // ────────────────────────────────────────────────────────────────────────
    // End of “notify followers” snippet
    // ────────────────────────────────────────────────────────────────────────
    //

    // Build the HTML snippet for the single new post
    $pid       = (int)$row['id'];
    $postedAt  = date('M j, Y H:i', strtotime($row['created_at']));
    $updatedAt = date('M j, Y H:i', strtotime($row['updated_at']));

    ob_start();
    ?>
    <div class="block block-rounded mb-4 profile-post"
         id="post-<?= $pid; ?>"
         data-post-id="<?= $pid; ?>"
         data-poster-id="<?= $currentUserId; ?>">
      <div class="block-header d-flex justify-content-between align-items-center">
        <h5 class="block-title post-title"><?= htmlspecialchars($row['title']); ?></h5>
        <div class="post-actions">
          <button class="btn btn-sm btn-link edit-post-btn" data-post-id="<?= $pid; ?>">
            <i class="fa fa-edit"></i>
          </button>
          <button class="btn btn-sm btn-link delete-post-btn text-danger" data-post-id="<?= $pid; ?>">
            <i class="fa fa-trash-alt"></i>
          </button>
        </div>
      </div>
      <div class="block-content">
        <div class="post-meta text-muted fs-xs mb-2">
          Posted <?= $postedAt; ?>
        </div>
        <div class="post-body mb-3">
          <?= nl2br(htmlspecialchars($row['message'])); ?>
        </div>
        <div class="post-reacts mb-2">
          <?php foreach (['like'=>'👍','love'=>'❤️','laugh'=>'😂','angry'=>'😡'] as $type=>$emoji): ?>
            <button class="btn btn-sm btn-outline-secondary react-btn"
                    data-post-id="<?= $pid; ?>"
                    data-react="<?= $type; ?>">
              <?= $emoji; ?>
              <span class="react-count" data-react="<?= $type; ?>">0</span>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php
    $html = ob_get_clean();

    echo json_encode([
      'success'   => true,
      'post_html' => $html
    ]);
    exit;
}

// 5) For “update” (formerly “edit”) and “delete,” first verify ownership
if ($action === 'update' || $action === 'delete') {
    $postId = (int)($_POST['post_id'] ?? 0);
    if ($postId <= 0) {
        echo json_encode([
          'success' => false,
          'error'   => 'Invalid post ID.'
        ]);
        exit;
    }

    // Fetch the post’s owner
    $fetchOwner = $pdo->prepare("
      SELECT user_id, title, message, created_at, updated_at
      FROM `demon_user_profile_posts`
      WHERE id = :pid
      LIMIT 1
    ");
    $fetchOwner->execute(['pid' => $postId]);
    $row = $fetchOwner->fetch(PDO::FETCH_ASSOC);

    if (!$row || (int)$row['user_id'] !== $currentUserId) {
        echo json_encode([
          'success' => false,
          'error'   => 'You can only modify your own posts.'
        ]);
        exit;
    }

    // If updating (previously “edit”):
    if ($action === 'update') {
        $title   = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($title === '' || $message === '') {
            echo json_encode([
              'success' => false,
              'error'   => 'Title and message cannot be empty.'
            ]);
            exit;
        }

        // Update the post
        $upd = $pdo->prepare("
          UPDATE `demon_user_profile_posts`
          SET title = :title, message = :msg, updated_at = NOW()
          WHERE id = :pid
        ");
        $upd->execute([
          'title' => $title,
          'msg'   => $message,
          'pid'   => $postId
        ]);

        // Re-fetch to get the new updated_at
        $refetch = $pdo->prepare("
          SELECT title, message, created_at, updated_at
          FROM `demon_user_profile_posts`
          WHERE id = :pid
          LIMIT 1
        ");
        $refetch->execute(['pid' => $postId]);
        $updatedRow = $refetch->fetch(PDO::FETCH_ASSOC);
        if (!$updatedRow) {
            echo json_encode([
              'success' => false,
              'error'   => 'Failed to reload post.'
            ]);
            exit;
        }

        $postedAt  = date('M j, Y H:i', strtotime($updatedRow['created_at']));
        $updatedAt = date('M j, Y H:i', strtotime($updatedRow['updated_at']));

        // Build the snippet for in‐place replacement
        ob_start();
        ?>
        <h5 class="block-title post-title"><?= htmlspecialchars($updatedRow['title']); ?></h5>
        <div class="post-meta text-muted fs-xs mb-2">
          Posted <?= $postedAt; ?>
          <?php if ($updatedRow['updated_at'] !== $updatedRow['created_at']): ?>
            &middot; Updated <?= $updatedAt; ?>
          <?php endif; ?>
        </div>
        <div class="post-body mb-3">
          <?= nl2br(htmlspecialchars($updatedRow['message'])); ?>
        </div>
        <?php
        $htmlSnippet = ob_get_clean();

        echo json_encode([
          'success'   => true,
          'post_id'   => $postId,
          'post_html' => $htmlSnippet
        ]);
        exit;
    }

    // If deleting:
    if ($action === 'delete') {
        $del = $pdo->prepare("DELETE FROM `demon_user_profile_posts` WHERE id = :pid");
        $del->execute(['pid' => $postId]);

        echo json_encode([
          'success' => true
        ]);
        exit;
    }
}

// Fallback
echo json_encode([
  'success' => false,
  'error'   => 'Unexpected error.'
]);
exit;
