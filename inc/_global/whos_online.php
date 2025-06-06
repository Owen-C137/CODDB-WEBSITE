<?php
// inc/_global/whos_online.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

$currentUserId = (int)($_SESSION['user_id'] ?? 0);

if ($currentUserId > 0) {
    $upd = $pdo->prepare("
        UPDATE `demon_users`
        SET last_activity = NOW()
        WHERE id = :uid
    ");
    $upd->execute(['uid' => $currentUserId]);
}

$onlineWindow = '5 MINUTE';
$stmt = $pdo->prepare("
    SELECT id, username
    FROM `demon_users`
    WHERE last_activity >= (NOW() - INTERVAL $onlineWindow)
    ORDER BY username ASC
");
$stmt->execute();
$onlineUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count users active in the last 24 hours
$dayStmt = $pdo->prepare("
    SELECT COUNT(*) AS count
    FROM `demon_users`
    WHERE last_activity >= (NOW() - INTERVAL 1 DAY)
");
$dayStmt->execute();
$usersLast24h = (int)$dayStmt->fetchColumn();
?>
<div class="row">

<div class="col-md-3">
  <div class="list-group shadow-sm">
    <div class="list-group-item active">
      <strong><i class="fa fa-users"></i> Online Users</strong>
    </div>
    <?php if (empty($onlineUsers)): ?>
      <div class="list-group-item text-muted small">
        No active users at the moment.
      </div>
    <?php else: ?>
      <?php foreach ($onlineUsers as $user): ?>
        <a href="/profile.php?uid=<?= (int)$user['id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
          <?= htmlspecialchars($user['username'], ENT_QUOTES); ?>
          <span class="badge bg-success rounded-pill">Online</span>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
    <div class="list-group-item text-muted small text-center">
      Users active in the last <?= $onlineWindow ?><br>
      <?= $usersLast24h ?> active in the past 24 hours
    </div>
  </div>
</div>

</div>