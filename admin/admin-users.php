<?php
// admin/admin-users.php

require __DIR__ . '/../inc/_global/config.php';
require __DIR__ . '/../inc/_global/login_check.php';
require __DIR__ . '/../inc/backend/config.php';

// Use UK timezone for display
date_default_timezone_set('Europe/London');

// Start session if not already (for flash messages)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: insert an audit log entry
function insert_audit_log(PDO $pdo, int $adminId, string $action, array $detailsArray, string $ip) {
    $logSql = "
        INSERT INTO `demon_audit_logs`
        (user_id, action, details, ip_address, created_at)
        VALUES
        (:uid, :action, :details, :ip, NOW())
    ";
    $logStmt = $pdo->prepare($logSql);
    $logStmt->execute([
        'uid'     => $adminId,
        'action'  => $action,
        'details' => json_encode($detailsArray),
        'ip'      => $ip
    ]);
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $ids = $_POST['selected_users'] ?? [];

    if (empty($ids)) {
        $_SESSION['flash_error'] = 'No users were selected.';
    } else {
        // Cast each selected ID to integer
        $ids = array_map('intval', $ids);

        // Prepare comma-separated placeholders for an IN() clause
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $adminId = (int)($_SESSION['user_id'] ?? 0);
        $ip      = $_SERVER['REMOTE_ADDR'] ?? '';

        try {
            switch ($action) {
                case 'activate':
                    // Set is_active = 1, status = 'active', and role_id = 2
                    $sql = "
                        UPDATE `demon_users`
                        SET is_active  = 1,
                            status     = 'active',
                            role_id    = 2,
                            updated_at = NOW()
                        WHERE id IN ($placeholders)
                    ";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($ids);
                    $count = $stmt->rowCount();
                    $_SESSION['flash_success'] = "$count user(s) activated successfully.";

                    // Audit log
                    insert_audit_log(
                        $pdo,
                        $adminId,
                        'user.bulk_activate',
                        ['user_ids' => $ids, 'count' => $count],
                        $ip
                    );
                    break;

                case 'ban':
                    // Set is_active = 3, status = 'banned', and role_id = 6 (or 7 as needed)
                    $sql = "
                        UPDATE `demon_users`
                        SET is_active  = 3,
                            status     = 'banned',
                            role_id    = 6,
                            updated_at = NOW()
                        WHERE id IN ($placeholders)
                    ";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($ids);
                    $count = $stmt->rowCount();
                    $_SESSION['flash_success'] = "$count user(s) banned successfully.";

                    // Audit log
                    insert_audit_log(
                        $pdo,
                        $adminId,
                        'user.bulk_ban',
                        ['user_ids' => $ids, 'count' => $count],
                        $ip
                    );
                    break;

                case 'delete':
                    // Delete selected users
                    $sql = "
                        DELETE FROM `demon_users`
                        WHERE id IN ($placeholders)
                    ";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($ids);
                    $count = $stmt->rowCount();
                    $_SESSION['flash_success'] = "$count user(s) deleted successfully.";

                    // Audit log
                    insert_audit_log(
                        $pdo,
                        $adminId,
                        'user.bulk_delete',
                        ['user_ids' => $ids, 'count' => $count],
                        $ip
                    );
                    break;

                default:
                    $_SESSION['flash_error'] = 'Invalid bulk action.';
                    break;
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'An error occurred: ' . htmlspecialchars($e->getMessage());
            // Optionally log the failure to audit as well
            insert_audit_log(
                $pdo,
                $adminId,
                'user.bulk_action_error',
                ['action' => $action, 'user_ids' => $ids, 'error' => $e->getMessage()],
                $ip
            );
        }
    }

    // Redirect back to this page to avoid resubmission
    header('Location: admin-users.php?page=' . (int)($_GET['page'] ?? 1) . '&per_page=' . (int)($_GET['per_page'] ?? 10));
    exit;
}

// Helper: compute relative time (e.g. "2 hours ago")
function relative_time(string $datetime): string {
    $timestamp = strtotime($datetime);
    $now = time();
    $diff = $now - $timestamp;

    if ($diff < 60) {
        return $diff . ' second' . ($diff !== 1 ? 's' : '') . ' ago';
    }
    if ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins !== 1 ? 's' : '') . ' ago';
    }
    if ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours !== 1 ? 's' : '') . ' ago';
    }
    if ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days !== 1 ? 's' : '') . ' ago';
    }
    $weeks = floor($diff / 604800);
    return $weeks . ' week' . ($weeks !== 1 ? 's' : '') . ' ago';
}

// Pagination parameters
$page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
if ($perPage <= 0) {
    $perPage = 10;
}
if ($page <= 0) {
    $page = 1;
}
$offset = ($page - 1) * $perPage;

// Count total users
$totalStmt  = $pdo->query("SELECT COUNT(*) AS cnt FROM `demon_users`");
$totalRow   = $totalStmt->fetch();
$totalUsers = (int)$totalRow['cnt'];
$totalPages = (int)ceil($totalUsers / $perPage);

// Fetch users with profile and role data
$stmt = $pdo->prepare("
    SELECT u.id,
           u.username,
           u.email,
           u.created_at AS registered_at,
           u.last_login,
           r.name AS role_name,
           u.profile_picture_url
    FROM `demon_users` AS u
    LEFT JOIN `demon_roles` AS r ON r.id = u.role_id
    LEFT JOIN `demon_user_profiles` AS up ON up.user_id = u.id
    ORDER BY u.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();
?>
<?php require __DIR__ . '/../inc/_global/views/head_start.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/head_end.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/page_start.php'; ?>

<!-- Page Content -->
<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="content-heading">All Users</h2>
    <!-- Bulk Actions Dropdown -->
    <div>
      <!-- Wrap dropdown inside the same form as the table -->
      <button type="button" class="btn btn-sm btn-alt-primary dropdown-toggle" id="bulkActions" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Bulk Actions
      </button>
      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="bulkActions">
        <!-- The buttons below submit the form with the corresponding action -->
        <button type="submit" form="users-form" name="bulk_action" value="activate" class="dropdown-item">
          Activate Selected
        </button>
        <button type="submit" form="users-form" name="bulk_action" value="ban" class="dropdown-item">
          Ban Selected
        </button>
        <button type="submit" form="users-form" name="bulk_action" value="delete" class="dropdown-item">
          Delete Selected
        </button>
        <div class="dropdown-divider"></div>
        <!-- You can implement “Change Role of Selected” similarly if needed -->
        <button type="button" class="dropdown-item disabled">
          Change Role of Selected
        </button>
      </div>
    </div>
  </div>

  <!-- Alert messages -->
  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success">
      <?= htmlspecialchars($_SESSION['flash_success']); ?>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger">
      <?= htmlspecialchars($_SESSION['flash_error']); ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <?php if (empty($users)): ?>
    <div class="alert alert-info text-center">
      No users found.
    </div>
  <?php else: ?>
    <!-- Begin form that wraps both bulk actions and the user table -->
    <form id="users-form" method="post" action="admin-users.php?page=<?= $page; ?>&per_page=<?= $perPage; ?>">
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th style="width: 1%;">
                <input class="form-check-input" type="checkbox" id="check-all">
              </th>
              <th>User</th>
              <th>User Group</th>
              <th>Registered</th>
              <th>Last Online</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <tr>
                <td>
                  <input class="form-check-input user-checkbox" type="checkbox" name="selected_users[]" value="<?= $user['id']; ?>">
                </td>
                <td class="d-flex align-items-center">
                  <?php if (!empty($user['profile_picture_url'])): ?>
                    <img src="<?= htmlspecialchars($user['profile_picture_url']); ?>" alt="Avatar" class="img-avatar img-avatar-xs me-2">
                  <?php else: ?>
                    <span class="img-avatar img-avatar-xs bg-secondary text-white me-2">
                      <?= strtoupper(substr($user['username'], 0, 1)); ?>
                    </span>
                  <?php endif; ?>
                  <div class="d-flex flex-column">
                    <span class="fw-semibold"><?= htmlspecialchars($user['username']); ?></span>
                    <span class="fs-xs text-muted"><?= htmlspecialchars($user['email']); ?></span>
                  </div>
                </td>
                <td><?= htmlspecialchars($user['role_name'] ?: '—'); ?></td>
                <td>
                  <?= date('M j, Y H:i', strtotime($user['registered_at'])); ?>
                </td>
                <td>
                  <?php if ($user['last_login']): ?>
                    <?= date('M j, Y H:i', strtotime($user['last_login'])); ?><br>
                    <span class="fs-xs text-muted">(<?= relative_time($user['last_login']); ?>)</span>
                  <?php else: ?>
                    <span class="text-muted">Never</span>
                  <?php endif; ?>
                </td>
                <td>
                  <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-toggle="tooltip" title="Edit">
                    <i class="fa fa-fw fa-pencil-alt"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-toggle="tooltip" title="Profile">
                    <i class="fa fa-fw fa-user"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="More">
                    <i class="fa fa-fw fa-ellipsis-h"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </form>

    <!-- Pagination Controls -->
    <div class="d-flex justify-content-between align-items-center mt-4">
      <!-- Per-Page Selector -->
      <div>
        <form method="get" class="d-inline-block">
          <label for="per_page" class="me-2">Users per page:</label>
          <select id="per_page" name="per_page" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
            <?php foreach ([5, 10, 25, 50] as $opt): ?>
              <option value="<?= $opt; ?>" <?= $perPage === $opt ? 'selected' : ''; ?>><?= $opt; ?></option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" name="page" value="1">
        </form>
      </div>

      <!-- Page Links -->
      <nav aria-label="User list pagination">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?= max(1, $page - 1); ?>&per_page=<?= $perPage; ?>">« Prev</a>
          </li>
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : ''; ?>">
              <a class="page-link" href="?page=<?= $p; ?>&per_page=<?= $perPage; ?>"><?= $p; ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= $page >= $totalPages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?= min($totalPages, $page + 1); ?>&per_page=<?= $perPage; ?>">Next »</a>
          </li>
        </ul>
      </nav>
    </div>
  <?php endif; ?>
</div>
<!-- END Page Content -->

<?php require __DIR__ . '/../inc/_global/views/page_end.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/footer_start.php'; ?>
<script>
  // “Check All” functionality
  document.getElementById('check-all').addEventListener('change', function() {
    const checked = this.checked;
    document.querySelectorAll('.user-checkbox').forEach(cb => {
      cb.checked = checked;
    });
  });
</script>
<?php require __DIR__ . '/../inc/_global/views/footer_end.php'; ?>
