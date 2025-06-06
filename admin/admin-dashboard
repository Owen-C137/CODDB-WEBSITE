<?php
// admin/admin-dashboard.php

require __DIR__ . '/../inc/_global/config.php';
require __DIR__ . '/../inc/_global/login_check.php';
require __DIR__ . '/../inc/backend/config.php';

// Use UK timezone
date_default_timezone_set('Europe/London');
session_start();

// Fetch summary statistics

// 1) User counts by status
$totalUsersStmt = $pdo->query("SELECT COUNT(*) AS cnt FROM `demon_users`");
$totalUsers     = (int)$totalUsersStmt->fetchColumn();

$activeUsersStmt = $pdo->query("SELECT COUNT(*) FROM `demon_users` WHERE is_active = 1");
$activeUsers     = (int)$activeUsersStmt->fetchColumn();

$pendingUsersStmt = $pdo->query("SELECT COUNT(*) FROM `demon_users` WHERE is_active = 0 AND status = 'pending'");
$pendingUsers     = (int)$pendingUsersStmt->fetchColumn();

$bannedUsersStmt = $pdo->query("SELECT COUNT(*) FROM `demon_users` WHERE status = 'banned'");
$bannedUsers     = (int)$bannedUsersStmt->fetchColumn();

// 2) Audit log count
$auditCountStmt = $pdo->query("SELECT COUNT(*) FROM `demon_audit_logs`");
$auditCount     = (int)$auditCountStmt->fetchColumn();

// 3) Login attempts count (e.g., last 30 days)
$loginAttemptsCountStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM `demon_login_attempts` 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$loginAttemptsCountStmt->execute();
$loginAttemptsCount = (int)$loginAttemptsCountStmt->fetchColumn();

// 4) Registration logs count (last 30 days)
$registrationCountStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM `demon_registration_logs` 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$registrationCountStmt->execute();
$registrationCount = (int)$registrationCountStmt->fetchColumn();

// 5) Active user sessions (expires in future)
$sessionsCountStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM `demon_user_sessions` 
    WHERE expires_at > NOW()
");
$sessionsCountStmt->execute();
$sessionsCount = (int)$sessionsCountStmt->fetchColumn();

// 6) Recent 5 Registrations
$recentRegsStmt = $pdo->query("
    SELECT email, ip_address, user_agent, created_at
    FROM `demon_registration_logs`
    ORDER BY created_at DESC
    LIMIT 5
");
$recentRegistrations = $recentRegsStmt->fetchAll();

// 7) Recent 5 Login Attempts
$recentLoginsStmt = $pdo->query("
    SELECT username_or_email, ip_address, was_successful, user_agent, created_at
    FROM `demon_login_attempts`
    ORDER BY created_at DESC
    LIMIT 5
");
$recentLoginAttempts = $recentLoginsStmt->fetchAll();

// 8) Recent 5 Audit Logs
$recentAuditStmt = $pdo->query("
    SELECT l.id, u.username AS actor, l.action, l.details, l.ip_address, l.created_at
    FROM `demon_audit_logs` AS l
    LEFT JOIN `demon_users` AS u ON u.id = l.user_id
    ORDER BY l.created_at DESC
    LIMIT 5
");
$recentAudits = $recentAuditStmt->fetchAll();

// 9) Total user roles breakdown
$rolesBreakdownStmt = $pdo->query("
    SELECT r.name, COUNT(u.id) AS cnt
    FROM `demon_roles` AS r
    LEFT JOIN `demon_users` AS u ON u.role_id = r.id
    GROUP BY r.id, r.name
    ORDER BY r.level DESC
");
$rolesBreakdown = $rolesBreakdownStmt->fetchAll();
?>

<?php
// Include Codebase header/sidebar/footer
$cb->inc_header  = __DIR__ . '/../inc/backend/views/inc_header.php';
$cb->inc_sidebar = __DIR__ . '/../inc/backend/views/inc_sidebar.php';
$cb->inc_footer  = __DIR__ . '/../inc/backend/views/inc_footer.php';
?>

<?php require __DIR__ . '/../inc/_global/views/head_start.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/head_end.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/page_start.php'; ?>

<!-- Page Content -->
<div class="content">
  <h2 class="content-heading">Admin Dashboard</h2>

  <!-- Top Summary Blocks -->
  <div class="row mb-4">
    <div class="col-6 col-md-3 col-xl-3">
      <div class="block text-center">
        <div class="block-content bg-body-light py-3">
          <p class="fw-medium mb-0">
            <i class="fa fa-fw fa-users fa-2x mb-2"></i> Total Users
          </p>
        </div>
        <div class="block-content">
          <p class="fs-1 mb-2">
            <strong><?= $totalUsers; ?></strong>
          </p>
          <span class="fs-sm text-muted">All registered accounts</span>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3 col-xl-3">
      <div class="block text-center">
        <div class="block-content bg-body-light py-3">
          <p class="fw-medium mb-0">
            <i class="fa fa-fw fa-user-check fa-2x mb-2"></i> Active Users
          </p>
        </div>
        <div class="block-content">
          <p class="fs-1 mb-2">
            <strong><?= $activeUsers; ?></strong>
          </p>
          <span class="fs-sm text-muted">Verified & Active</span>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3 col-xl-3">
      <div class="block text-center">
        <div class="block-content bg-body-light py-3">
          <p class="fw-medium mb-0">
            <i class="fa fa-fw fa-user-clock fa-2x mb-2"></i> Pending Activations
          </p>
        </div>
        <div class="block-content">
          <p class="fs-1 mb-2">
            <strong><?= $pendingUsers; ?></strong>
          </p>
          <span class="fs-sm text-muted">Awaiting Email Confirm</span>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3 col-xl-3">
      <div class="block text-center">
        <div class="block-content bg-body-light py-3">
          <p class="fw-medium mb-0">
            <i class="fa fa-fw fa-user-slash fa-2x mb-2"></i> Banned Users
          </p>
        </div>
        <div class="block-content">
          <p class="fs-1 mb-2">
            <strong><?= $bannedUsers; ?></strong>
          </p>
          <span class="fs-sm text-muted">Disabled Accounts</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Logs Summary Blocks -->
  <div class="row mb-4">
    <div class="col-6 col-md-3 col-xl-3">
      <div class="block text-center">
        <div class="block-content bg-body-light py-3">
          <p class="fw-medium mb-0">
            <i class="fa fa-fw fa-clipboard-list fa-2x mb-2"></i> Audit Logs (30d)
          </p>
        </div>
        <div class="block-content">
          <p class="fs-1 mb-2">
            <strong><?= $auditCount; ?></strong>
          </p>
          <a href="?export=audit" class="btn btn-sm btn-outline-primary mb-1">
            <i class="fa fa-file-csv me-1"></i> Export CSV
          </a>
          <button
            type="button"
            class="btn btn-sm btn-outline-danger"
            data-bs-toggle="modal"
            data-bs-target="#modal-confirm-clear"
            data-action="clear_audit"
            data-label="Audit Logs">
            Clear All
          </button>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3 col-xl-3">
      <div class="block text-center">
        <div class="block-content bg-body-light py-3">
          <p class="fw-medium mb-0">
            <i class="fa fa-fw fa-sign-in-alt fa-2x mb-2"></i> Login Attempts (30d)
          </p>
        </div>
        <div class="block-content">
          <p class="fs-1 mb-2">
            <strong><?= $loginAttemptsCount; ?></strong>
          </p>
          <a href="?export=login" class="btn btn-sm btn-outline-primary mb-1">
            <i class="fa fa-file-csv me-1"></i> Export CSV
          </a>
          <button
            type="button"
            class="btn btn-sm btn-outline-danger"
            data-bs-toggle="modal"
            data-bs-target="#modal-confirm-clear"
            data-action="clear_login"
            data-label="Login Attempts">
            Clear All
          </button>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3 col-xl-3">
      <div class="block text-center">
        <div class="block-content bg-body-light py-3">
          <p class="fw-medium mb-0">
            <i class="fa fa-fw fa-user-plus fa-2x mb-2"></i> Registrations (30d)
          </p>
        </div>
        <div class="block-content">
          <p class="fs-1 mb-2">
            <strong><?= $registrationCount; ?></strong>
          </p>
          <a href="?export=registration" class="btn btn-sm btn-outline-primary mb-1">
            <i class="fa fa-file-csv me-1"></i> Export CSV
          </a>
          <button
            type="button"
            class="btn btn-sm btn-outline-danger"
            data-bs-toggle="modal"
            data-bs-target="#modal-confirm-clear"
            data-action="clear_registration"
            data-label="Registration Logs">
            Clear All
          </button>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3 col-xl-3">
      <div class="block text-center">
        <div class="block-content bg-body-light py-3">
          <p class="fw-medium mb-0">
            <i class="fa fa-fw fa-user-clock fa-2x mb-2"></i> Active Sessions
          </p>
        </div>
        <div class="block-content">
          <p class="fs-1 mb-2">
            <strong><?= $sessionsCount; ?></strong>
          </p>
          <a href="?export=sessions" class="btn btn-sm btn-outline-primary mb-1">
            <i class="fa fa-file-csv me-1"></i> Export CSV
          </a>
          <button
            type="button"
            class="btn btn-sm btn-outline-danger"
            data-bs-toggle="modal"
            data-bs-target="#modal-confirm-clear"
            data-action="clear_sessions"
            data-label="User Sessions">
            Clear All
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Role Breakdown Chart -->
  <div class="block mb-4">
    <div class="block-header block-header-default">
      <h3 class="block-title">User Roles Breakdown</h3>
    </div>
    <div class="block-content block-content-full">
      <canvas id="rolesChart" style="height: 200px;"></canvas>
    </div>
  </div>

  <!-- Recent Tables -->
  <div class="row">
    <!-- Recent Registrations -->
    <div class="col-md-6 mb-4">
      <div class="block">
        <div class="block-header block-header-default">
          <h3 class="block-title">Recent Registrations</h3>
        </div>
        <div class="block-content">
          <table class="table table-striped table-hover fs-sm">
            <thead>
              <tr>
                <th>Email</th>
                <th>IP Address</th>
                <th>User Agent</th>
                <th>Time</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentRegistrations as $reg): ?>
                <tr>
                  <td><?= htmlspecialchars($reg['email']); ?></td>
                  <td><?= htmlspecialchars($reg['ip_address']); ?></td>
                  <td class="text-truncate" style="max-width: 200px;"><?= htmlspecialchars($reg['user_agent']); ?></td>
                  <td><?= date('M j, Y H:i', strtotime($reg['created_at'])); ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($recentRegistrations)): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted">No recent registrations</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Recent Login Attempts -->
    <div class="col-md-6 mb-4">
      <div class="block">
        <div class="block-header block-header-default">
          <h3 class="block-title">Recent Login Attempts</h3>
        </div>
        <div class="block-content">
          <table class="table table-striped table-hover fs-sm">
            <thead>
              <tr>
                <th>Credential</th>
                <th>IP Address</th>
                <th>Success</th>
                <th>Time</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentLoginAttempts as $login): ?>
                <tr>
                  <td><?= htmlspecialchars($login['username_or_email']); ?></td>
                  <td><?= htmlspecialchars($login['ip_address']); ?></td>
                  <td>
                    <?php if ($login['was_successful']): ?>
                      <span class="badge bg-success">Yes</span>
                    <?php else: ?>
                      <span class="badge bg-danger">No</span>
                    <?php endif; ?>
                  </td>
                  <td><?= date('M j, Y H:i', strtotime($login['created_at'])); ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($recentLoginAttempts)): ?>
                <tr>
                  <td colspan="4" class="text-center text-muted">No recent login attempts</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Audit Logs -->
  <div class="block mb-4">
    <div class="block-header block-header-default">
      <h3 class="block-title">Recent Audit Logs</h3>
    </div>
    <div class="block-content">
      <table class="table table-striped table-hover fs-sm">
        <thead>
          <tr>
            <th>#</th>
            <th>Actor</th>
            <th>Action</th>
            <th>Details</th>
            <th>IP</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentAudits as $aud): ?>
            <tr>
              <td><?= $aud['id']; ?></td>
              <td><?= htmlspecialchars($aud['actor'] ?? 'System'); ?></td>
              <td><?= htmlspecialchars($aud['action']); ?></td>
              <td class="text-truncate" style="max-width: 200px;"><?= htmlspecialchars($aud['details']); ?></td>
              <td><?= htmlspecialchars($aud['ip_address']); ?></td>
              <td><?= date('M j, Y H:i', strtotime($aud['created_at'])); ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($recentAudits)): ?>
            <tr>
              <td colspan="6" class="text-center text-muted">No recent audit logs</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<!-- END Page Content -->

<?php require __DIR__ . '/../inc/_global/views/page_end.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/footer_start.php'; ?>

<!-- Chart.js (for roles breakdown) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('rolesChart').getContext('2d');
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: [<?php
          echo implode(',', array_map(function($r) {
            return "'" . addslashes($r['name']) . "'";
          }, $rolesBreakdown));
        ?>],
        datasets: [{
          data: [<?php
            echo implode(',', array_map(function($r) {
              return (int)$r['cnt'];
            }, $rolesBreakdown));
          ?>],
          backgroundColor: [
            '#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  });
</script>

<?php require __DIR__ . '/../inc/_global/views/footer_end.php'; ?>
