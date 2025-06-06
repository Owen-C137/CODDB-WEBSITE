<?php
// notifications.php

require 'inc/_global/config.php';
require 'inc/_global/login_check.php';

$userId = $_SESSION['user_id'];

// Mark all unread notifications as read
$updateAll = $pdo->prepare("
  UPDATE `demon_notifications`
  SET is_read = 1
  WHERE user_id = :uid
    AND is_read = 0
");
$updateAll->execute([':uid' => $userId]);

// Fetch all notifications for this user (most recent first)
$sql = "
  SELECT id, message, link, created_at
  FROM `demon_notifications`
  WHERE user_id = :uid
  ORDER BY created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $userId]);
$allNotifications = $stmt->fetchAll();
?>

<?php require 'inc/_global/views/head_start.php'; ?>
<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="content-heading">All Notifications</h2>
    <a href="dashboard.php" class="btn btn-sm btn-alt-primary">
      <i class="fa fa-arrow-left me-1"></i> Back to Dashboard
    </a>
  </div>

  <?php if (empty($allNotifications)): ?>
    <div class="alert alert-info">You have no notifications.</div>
  <?php else: ?>
    <div class="list-group">
      <?php foreach ($allNotifications as $notif): ?>
        <a href="<?= htmlspecialchars($notif['link'] ?? '#'); ?>"
           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
          <div>
            <?= htmlspecialchars($notif['message']); ?>
            <div class="text-muted fs-xs">
              <?= date('M j, Y H:i', strtotime($notif['created_at'])); ?>
            </div>
          </div>
          <i class="fa fa-chevron-right opacity-50"></i>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>
<?php require 'inc/_global/views/footer_end.php'; ?>
