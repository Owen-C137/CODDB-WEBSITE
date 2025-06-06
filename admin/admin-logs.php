<?php
// admin/admin-logs.php

require __DIR__ . '/../inc/_global/config.php';
require __DIR__ . '/../inc/_global/login_check.php';
require __DIR__ . '/../inc/backend/config.php';

// Use UK timezone for display
date_default_timezone_set('Europe/London');

// Start session if not already (for flash messages)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Handle Single‐Row Delete ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_action'], $_POST['delete_id'])) {
    $type = $_POST['delete_action'];           // e.g. "delete_audit", "delete_login", etc.
    $id   = (int)$_POST['delete_id'];

    try {
        switch ($type) {
            case 'delete_audit':
                $stmt = $pdo->prepare("DELETE FROM `demon_audit_logs` WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $_SESSION['flash_success'] = "Deleted audit log entry #{$id}.";
                break;
            case 'delete_login':
                $stmt = $pdo->prepare("DELETE FROM `demon_login_attempts` WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $_SESSION['flash_success'] = "Deleted login attempt entry #{$id}.";
                break;
            case 'delete_registration':
                $stmt = $pdo->prepare("DELETE FROM `demon_registration_logs` WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $_SESSION['flash_success'] = "Deleted registration log entry #{$id}.";
                break;
            case 'delete_session':
                $stmt = $pdo->prepare("DELETE FROM `demon_user_sessions` WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $_SESSION['flash_success'] = "Deleted user session entry #{$id}.";
                break;
            default:
                $_SESSION['flash_error'] = 'Invalid delete action.';
        }
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Failed to delete entry: ' . htmlspecialchars($e->getMessage());
    }
    header('Location: admin-logs.php?' . $_SERVER['QUERY_STRING']);
    exit;
}

// ── Handle “Clear All” Actions ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_action']) && !isset($_POST['delete_action'])) {
    $action = $_POST['clear_action'];
    try {
        switch ($action) {
            case 'clear_audit':
                $pdo->exec("TRUNCATE TABLE `demon_audit_logs`");
                $_SESSION['flash_success'] = 'All audit logs have been cleared.';
                break;
            case 'clear_login':
                $pdo->exec("TRUNCATE TABLE `demon_login_attempts`");
                $_SESSION['flash_success'] = 'All login attempts have been cleared.';
                break;
            case 'clear_registration':
                $pdo->exec("TRUNCATE TABLE `demon_registration_logs`");
                $_SESSION['flash_success'] = 'All registration logs have been cleared.';
                break;
            case 'clear_sessions':
                $pdo->exec("TRUNCATE TABLE `demon_user_sessions`");
                $_SESSION['flash_success'] = 'All user sessions have been cleared.';
                break;
            default:
                $_SESSION['flash_error'] = 'Invalid clear action.';
        }
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Failed to clear logs: ' . htmlspecialchars($e->getMessage());
    }
    header('Location: admin-logs.php');
    exit;
}

// ── Handle Export to CSV ──────────────────────────────────────────────────────
if (isset($_GET['export'])) {
    $validTypes = ['audit','login','registration','sessions'];
    $exportType = trim($_GET['export']);

    // Only proceed if exportType is one of our valid options
    if (in_array($exportType, $validTypes, true)) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="logs_' . $exportType . '.csv"');
        $out = fopen('php://output', 'w');

        switch ($exportType) {
            case 'audit':
                fputcsv($out, ['ID','User','Action','Details','IP Address','Timestamp']);
                $query = "
                    SELECT al.id,
                           COALESCE(u.username,'System') AS user_name,
                           al.action,
                           al.details,
                           al.ip_address,
                           al.created_at
                    FROM `demon_audit_logs` AS al
                    LEFT JOIN `demon_users` AS u ON u.id = al.user_id
                    ORDER BY al.created_at DESC
                ";
                foreach ($pdo->query($query) as $row) {
                    fputcsv($out, [
                        $row['id'],
                        $row['user_name'],
                        $row['action'],
                        $row['details'],
                        $row['ip_address'],
                        $row['created_at']
                    ]);
                }
                break;

            case 'login':
                fputcsv($out, ['ID','User','Credential','IP Address','Success','User Agent','Timestamp']);
                $query = "
                    SELECT la.id,
                           COALESCE(u.username,'Unknown') AS user_name,
                           la.username_or_email,
                           la.ip_address,
                           la.was_successful,
                           la.user_agent,
                           la.created_at
                    FROM `demon_login_attempts` AS la
                    LEFT JOIN `demon_users` AS u ON u.id = la.user_id
                    ORDER BY la.created_at DESC
                ";
                foreach ($pdo->query($query) as $row) {
                    fputcsv($out, [
                        $row['id'],
                        $row['user_name'],
                        $row['username_or_email'],
                        $row['ip_address'],
                        $row['was_successful'] ? 'Yes' : 'No',
                        $row['user_agent'],
                        $row['created_at']
                    ]);
                }
                break;

            case 'registration':
                fputcsv($out, ['ID','IP Address','Email','User Agent','Referrer','Timestamp']);
                $query = "
                    SELECT id, ip_address, email, user_agent, referrer, created_at
                    FROM `demon_registration_logs`
                    ORDER BY created_at DESC
                ";
                foreach ($pdo->query($query) as $row) {
                    fputcsv($out, [
                        $row['id'],
                        $row['ip_address'],
                        $row['email'],
                        $row['user_agent'],
                        $row['referrer'],
                        $row['created_at']
                    ]);
                }
                break;

            case 'sessions':
                fputcsv($out, ['ID','User','Type','IP Address','User Agent','Created At','Expires At']);
                $query = "
                    SELECT s.id,
                           COALESCE(u.username,'Unknown') AS user_name,
                           s.session_type,
                           s.ip_address,
                           s.user_agent,
                           s.created_at,
                           s.expires_at
                    FROM `demon_user_sessions` AS s
                    LEFT JOIN `demon_users` AS u ON u.id = s.user_id
                    ORDER BY s.created_at DESC
                ";
                foreach ($pdo->query($query) as $row) {
                    fputcsv($out, [
                        $row['id'],
                        $row['user_name'],
                        $row['session_type'],
                        $row['ip_address'],
                        $row['user_agent'],
                        $row['created_at'],
                        $row['expires_at']
                    ]);
                }
                break;
        }

        fclose($out);
        exit;
    }
    // If exportType is not valid or empty, fall through—do not download anything.
}

// ── Fetch Summary Counts ──────────────────────────────────────────────────────
$auditCount         = (int)$pdo->query("SELECT COUNT(*) FROM `demon_audit_logs`")->fetchColumn();
$loginAttemptsCount = (int)$pdo->query("SELECT COUNT(*) FROM `demon_login_attempts`")->fetchColumn();
$registrationCount  = (int)$pdo->query("SELECT COUNT(*) FROM `demon_registration_logs`")->fetchColumn();
$sessionsCount      = (int)$pdo->query("SELECT COUNT(*) FROM `demon_user_sessions`")->fetchColumn();

// ── Filtering: read GET parameters for each tab ────────────────────────────────
$auditFrom       = $_GET['audit_from']       ?? '';
$auditTo         = $_GET['audit_to']         ?? '';
$auditSearch     = trim($_GET['audit_search'] ?? '');

$loginFrom       = $_GET['login_from']       ?? '';
$loginTo         = $_GET['login_to']         ?? '';
$loginSearch     = trim($_GET['login_search'] ?? '');

$regFrom         = $_GET['reg_from']         ?? '';
$regTo           = $_GET['reg_to']           ?? '';
$regSearch       = trim($_GET['reg_search']  ?? '');

$sessFrom        = $_GET['sess_from']        ?? '';
$sessTo          = $_GET['sess_to']          ?? '';
$sessTypeFilter  = $_GET['sess_type']        ?? '';

// ── Build WHERE clauses for each query ────────────────────────────────────────
$auditWhere = [];
$auditParams = [];
if ($auditFrom !== '') {
    $auditWhere[] = "al.created_at >= :audit_from";
    $auditParams['audit_from'] = "$auditFrom 00:00:00";
}
if ($auditTo !== '') {
    $auditWhere[] = "al.created_at <= :audit_to";
    $auditParams['audit_to'] = "$auditTo 23:59:59";
}
if ($auditSearch !== '') {
    $auditWhere[] = "(al.action LIKE :audit_search OR u.username LIKE :audit_search)";
    $auditParams['audit_search'] = "%{$auditSearch}%";
}
$auditWhereSql = $auditWhere ? 'WHERE ' . implode(' AND ', $auditWhere) : '';

$loginWhere = [];
$loginParams = [];
if ($loginFrom !== '') {
    $loginWhere[] = "la.created_at >= :login_from";
    $loginParams['login_from'] = "$loginFrom 00:00:00";
}
if ($loginTo !== '') {
    $loginWhere[] = "la.created_at <= :login_to";
    $loginParams['login_to'] = "$loginTo 23:59:59";
}
if ($loginSearch !== '') {
    $loginWhere[] = "(la.username_or_email LIKE :login_search OR u.username LIKE :login_search)";
    $loginParams['login_search'] = "%{$loginSearch}%";
}
$loginWhereSql = $loginWhere ? 'WHERE ' . implode(' AND ', $loginWhere) : '';

$regWhere = [];
$regParams = [];
if ($regFrom !== '') {
    $regWhere[] = "created_at >= :reg_from";
    $regParams['reg_from'] = "$regFrom 00:00:00";
}
if ($regTo !== '') {
    $regWhere[] = "created_at <= :reg_to";
    $regParams['reg_to'] = "$regTo 23:59:59";
}
if ($regSearch !== '') {
    $regWhere[] = "email LIKE :reg_search";
    $regParams['reg_search'] = "%{$regSearch}%";
}
$regWhereSql = $regWhere ? 'WHERE ' . implode(' AND ', $regWhere) : '';

$sessWhere = [];
$sessParams = [];
if ($sessFrom !== '') {
    $sessWhere[] = "s.created_at >= :sess_from";
    $sessParams['sess_from'] = "$sessFrom 00:00:00";
}
if ($sessTo !== '') {
    $sessWhere[] = "s.created_at <= :sess_to";
    $sessParams['sess_to'] = "$sessTo 23:59:59";
}
if ($sessTypeFilter !== '') {
    $sessWhere[] = "s.session_type = :sess_type";
    $sessParams['sess_type'] = $sessTypeFilter;
}
$sessWhereSql = $sessWhere ? 'WHERE ' . implode(' AND ', $sessWhere) : '';

// ── Fetch Recent Entries (limit 50) ───────────────────────────────────────────
$auditSql = "
  SELECT al.id,
         al.user_id,
         COALESCE(u.username,'System') AS user_name,
         al.action,
         al.details,
         al.ip_address,
         al.created_at
    FROM `demon_audit_logs` AS al
    LEFT JOIN `demon_users` AS u ON u.id = al.user_id
    {$auditWhereSql}
    ORDER BY al.created_at DESC
    LIMIT 50
";
$stmt = $pdo->prepare($auditSql);
$stmt->execute($auditParams);
$auditEntries = $stmt->fetchAll();

$loginSql = "
  SELECT la.id,
         la.user_id,
         COALESCE(u.username,'Unknown') AS user_name,
         la.username_or_email,
         la.ip_address,
         la.was_successful,
         la.user_agent,
         la.created_at
    FROM `demon_login_attempts` AS la
    LEFT JOIN `demon_users` AS u ON u.id = la.user_id
    {$loginWhereSql}
    ORDER BY la.created_at DESC
    LIMIT 50
";
$stmt = $pdo->prepare($loginSql);
$stmt->execute($loginParams);
$loginEntries = $stmt->fetchAll();

$regSql = "
  SELECT id, ip_address, email, user_agent, referrer, created_at
    FROM `demon_registration_logs`
    {$regWhereSql}
    ORDER BY created_at DESC
    LIMIT 50
";
$stmt = $pdo->prepare($regSql);
$stmt->execute($regParams);
$registrationEntries = $stmt->fetchAll();

$sessSql = "
  SELECT s.id,
         s.user_id,
         COALESCE(u.username,'Unknown') AS user_name,
         s.session_type,
         s.ip_address,
         s.user_agent,
         s.created_at,
         s.expires_at
    FROM `demon_user_sessions` AS s
    LEFT JOIN `demon_users` AS u ON u.id = s.user_id
    {$sessWhereSql}
    ORDER BY s.created_at DESC
    LIMIT 50
";
$stmt = $pdo->prepare($sessSql);
$stmt->execute($sessParams);
$sessionEntries = $stmt->fetchAll();

// Include header/sidebar/footer
$cb->inc_header  = __DIR__ . '/../inc/backend/views/inc_header.php';
$cb->inc_sidebar = __DIR__ . '/../inc/backend/views/inc_sidebar.php';
$cb->inc_footer  = __DIR__ . '/../inc/backend/views/inc_footer.php';

// Page‐specific layout
$cb->l_m_content = 'narrow';
?>
<?php require __DIR__ . '/../inc/_global/views/head_start.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/head_end.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/page_start.php'; ?>

<div class="content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="content-heading">System Logs &amp; Audit Trail</h2>
    <a href="javascript:void(0)" class="btn btn-alt-secondary" data-bs-toggle="modal" data-bs-target="#modal-normal">
      <i class="fa fa-fw fa-question-circle me-1"></i> Help
    </a>
  </div>

  <!-- Flash Messages -->
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

<!-- Summary Blocks -->
<div class="row mb-4">
  <div class="col-6 col-md-3 col-xl-3">
    <div class="block text-center">
      <div class="block-content bg-body-light py-3">
        <p class="fw-medium mb-0">
          <i class="fa fa-fw fa-clipboard-list fa-2x mb-2"></i> Audit Logs
        </p>
      </div>
      <div class="block-content">
        <p class="fs-1">
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
          <i class="fa fa-fw fa-sign-in-alt fa-2x mb-2"></i> Login Attempts
        </p>
      </div>
      <div class="block-content">
        <p class="fs-1">
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
          <i class="fa fa-fw fa-user-plus fa-2x mb-2"></i> Registration Logs
        </p>
      </div>
      <div class="block-content">
        <p class="fs-1">
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
          <i class="fa fa-fw fa-user-clock fa-2x mb-2"></i> User Sessions
        </p>
      </div>
      <div class="block-content">
        <p class="fs-1">
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


  <!-- Tabs for Each Log Type -->
  <ul class="nav nav-tabs mb-3" id="logsTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button
        class="nav-link active"
        id="audit-tab"
        data-bs-toggle="tab"
        data-bs-target="#audit"
        type="button"
        role="tab"
        aria-controls="audit"
        aria-selected="true">
        Audit Logs
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button
        class="nav-link"
        id="login-tab"
        data-bs-toggle="tab"
        data-bs-target="#login"
        type="button"
        role="tab"
        aria-controls="login"
        aria-selected="false">
        Login Attempts
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button
        class="nav-link"
        id="registration-tab"
        data-bs-toggle="tab"
        data-bs-target="#registration"
        type="button"
        role="tab"
        aria-controls="registration"
        aria-selected="false">
        Registration Logs
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button
        class="nav-link"
        id="sessions-tab"
        data-bs-toggle="tab"
        data-bs-target="#sessions"
        type="button"
        role="tab"
        aria-controls="sessions"
        aria-selected="false">
        User Sessions
      </button>
    </li>
  </ul>

  <div class="tab-content" id="logsTabContent">
    <!-- Audit Logs Tab -->
    <div class="tab-pane fade show active" id="audit" role="tabpanel" aria-labelledby="audit-tab">
      <!-- Filter Form (no name="export") -->
      <form method="get" class="row g-2 align-items-end mb-3">
        <div class="col-md-3">
          <label class="form-label" for="audit_from">From</label>
          <input type="date" id="audit_from" name="audit_from" class="form-control"
                 value="<?= htmlspecialchars($auditFrom); ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label" for="audit_to">To</label>
          <input type="date" id="audit_to" name="audit_to" class="form-control"
                 value="<?= htmlspecialchars($auditTo); ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="audit_search">Action/User</label>
          <input type="text" id="audit_search" name="audit_search" class="form-control"
                 placeholder="Search..."
                 value="<?= htmlspecialchars($auditSearch); ?>">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-alt-primary w-100">Filter</button>
        </div>
      </form>

      <?php if (empty($auditEntries)): ?>
        <div class="alert alert-info text-center">No audit log entries found.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm table-striped">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
                <th>IP</th>
                <th>Timestamp</th>
                <th>Delete</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($auditEntries as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']); ?></td>
                  <td><?= htmlspecialchars($row['user_name']); ?></td>
                  <td><?= htmlspecialchars($row['action']); ?></td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-secondary"
                      data-bs-container="body"
                      data-bs-toggle="popover"
                      data-bs-trigger="focus"
                      title="Details"
                      data-bs-content="<?= htmlspecialchars($row['details']); ?>">
                      View
                    </button>
                  </td>
                  <td><?= htmlspecialchars($row['ip_address']); ?></td>
                  <td><?= date('Y-m-d H:i:s', strtotime($row['created_at'])); ?></td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-danger"
                      data-bs-toggle="modal"
                      data-bs-target="#modal-confirm-delete"
                      data-action="delete_audit"
                      data-id="<?= $row['id']; ?>"
                      data-label="Audit Entry #<?= $row['id']; ?>">
                      <i class="fa fa-trash"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Login Attempts Tab -->
    <div class="tab-pane fade" id="login" role="tabpanel" aria-labelledby="login-tab">
      <!-- Filter Form -->
      <form method="get" class="row g-2 align-items-end mb-3">
        <div class="col-md-3">
          <label class="form-label" for="login_from">From</label>
          <input type="date" id="login_from" name="login_from" class="form-control"
                 value="<?= htmlspecialchars($loginFrom); ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label" for="login_to">To</label>
          <input type="date" id="login_to" name="login_to" class="form-control"
                 value="<?= htmlspecialchars($loginTo); ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="login_search">Username/Email</label>
          <input type="text" id="login_search" name="login_search" class="form-control"
                 placeholder="Search..."
                 value="<?= htmlspecialchars($loginSearch); ?>">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-alt-primary w-100">Filter</button>
        </div>
      </form>

      <?php if (empty($loginEntries)): ?>
        <div class="alert alert-info text-center">No login attempt entries found.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm table-striped">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>User</th>
                <th>Credential</th>
                <th>IP</th>
                <th>Success</th>
                <th>User Agent</th>
                <th>Timestamp</th>
                <th>Delete</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($loginEntries as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']); ?></td>
                  <td><?= htmlspecialchars($row['user_name']); ?></td>
                  <td><?= htmlspecialchars($row['username_or_email']); ?></td>
                  <td><?= htmlspecialchars($row['ip_address']); ?></td>
                  <td>
                    <?php if ($row['was_successful']): ?>
                      <span class="badge bg-success">Yes</span>
                    <?php else: ?>
                      <span class="badge bg-danger">No</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-secondary"
                      data-bs-container="body"
                      data-bs-toggle="popover"
                      data-bs-trigger="focus"
                      title="User Agent"
                      data-bs-content="<?= htmlspecialchars($row['user_agent']); ?>">
                      View
                    </button>
                  </td>
                  <td><?= date('Y-m-d H:i:s', strtotime($row['created_at'])); ?></td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-danger"
                      data-bs-toggle="modal"
                      data-bs-target="#modal-confirm-delete"
                      data-action="delete_login"
                      data-id="<?= $row['id']; ?>"
                      data-label="Login Attempt #<?= $row['id']; ?>">
                      <i class="fa fa-trash"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Registration Logs Tab -->
    <div class="tab-pane fade" id="registration" role="tabpanel" aria-labelledby="registration-tab">
      <!-- Filter Form -->
      <form method="get" class="row g-2 align-items-end mb-3">
        <div class="col-md-3">
          <label class="form-label" for="reg_from">From</label>
          <input type="date" id="reg_from" name="reg_from" class="form-control"
                 value="<?= htmlspecialchars($regFrom); ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label" for="reg_to">To</label>
          <input type="date" id="reg_to" name="reg_to" class="form-control"
                 value="<?= htmlspecialchars($regTo); ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="reg_search">Email</label>
          <input type="text" id="reg_search" name="reg_search" class="form-control"
                 placeholder="Search..."
                 value="<?= htmlspecialchars($regSearch); ?>">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-alt-primary w-100">Filter</button>
        </div>
      </form>

      <?php if (empty($registrationEntries)): ?>
        <div class="alert alert-info text-center">No registration log entries found.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm table-striped">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>IP</th>
                <th>Email</th>
                <th>User Agent</th>
                <th>Referrer</th>
                <th>Timestamp</th>
                <th>Delete</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($registrationEntries as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']); ?></td>
                  <td><?= htmlspecialchars($row['ip_address']); ?></td>
                  <td><?= htmlspecialchars($row['email']); ?></td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-secondary"
                      data-bs-container="body"
                      data-bs-toggle="popover"
                      data-bs-trigger="focus"
                      title="User Agent"
                      data-bs-content="<?= htmlspecialchars($row['user_agent']); ?>">
                      View
                    </button>
                  </td>
                  <td>
                    <?php if (!empty($row['referrer'])): ?>
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        data-bs-container="body"
                        data-bs-toggle="popover"
                        data-bs-trigger="focus"
                        title="Referrer"
                        data-bs-content="<?= htmlspecialchars($row['referrer']); ?>">
                        View
                      </button>
                    <?php else: ?>
                      —
                    <?php endif; ?>
                  </td>
                  <td><?= date('Y-m-d H:i:s', strtotime($row['created_at'])); ?></td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-danger"
                      data-bs-toggle="modal"
                      data-bs-target="#modal-confirm-delete"
                      data-action="delete_registration"
                      data-id="<?= $row['id']; ?>"
                      data-label="Registration Entry #<?= $row['id']; ?>">
                      <i class="fa fa-trash"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- User Sessions Tab -->
    <div class="tab-pane fade" id="sessions" role="tabpanel" aria-labelledby="sessions-tab">
      <!-- Filter Form -->
      <form method="get" class="row g-2 align-items-end mb-3">
        <div class="col-md-3">
          <label class="form-label" for="sess_from">From</label>
          <input type="date" id="sess_from" name="sess_from" class="form-control"
                 value="<?= htmlspecialchars($sessFrom); ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label" for="sess_to">To</label>
          <input type="date" id="sess_to" name="sess_to" class="form-control"
                 value="<?= htmlspecialchars($sessTo); ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="sess_type">Type</label>
          <select id="sess_type" name="sess_type" class="form-select">
            <option value="">— Any —</option>
            <option value="remember_me" <?= $sessTypeFilter === 'remember_me' ? 'selected' : ''; ?>>remember_me</option>
            <!-- Add other possible session_type values here -->
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-alt-primary w-100">Filter</button>
        </div>
      </form>

      <?php if (empty($sessionEntries)): ?>
        <div class="alert alert-info text-center">No user session entries found.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm table-striped">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>User</th>
                <th>Type</th>
                <th>IP</th>
                <th>User Agent</th>
                <th>Created At</th>
                <th>Expires At</th>
                <th>Delete</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($sessionEntries as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']); ?></td>
                  <td><?= htmlspecialchars($row['user_name']); ?></td>
                  <td><?= htmlspecialchars($row['session_type']); ?></td>
                  <td><?= htmlspecialchars($row['ip_address']); ?></td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-secondary"
                      data-bs-container="body"
                      data-bs-toggle="popover"
                      data-bs-trigger="focus"
                      title="User Agent"
                      data-bs-content="<?= htmlspecialchars($row['user_agent']); ?>">
                      View
                    </button>
                  </td>
                  <td><?= date('Y-m-d H:i:s', strtotime($row['created_at'])); ?></td>
                  <td><?= date('Y-m-d H:i:s', strtotime($row['expires_at'])); ?></td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-danger"
                      data-bs-toggle="modal"
                      data-bs-target="#modal-confirm-delete"
                      data-action="delete_session"
                      data-id="<?= $row['id']; ?>"
                      data-label="User Session #<?= $row['id']; ?>">
                      <i class="fa fa-trash"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal: Confirm Clear / Confirm Delete (uses provided “modal-normal” style) -->
<div class="modal" id="modal-confirm-clear" tabindex="-1" role="dialog" aria-labelledby="modal-confirm-clear" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="form-confirm-clear" method="POST" action="admin-logs.php" class="modal-content">
      <div class="block block-rounded shadow-none mb-0">
        <div class="block-header block-header-default">
          <h3 class="block-title" id="modalConfirmTitle">Confirm Action</h3>
          <div class="block-options">
            <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
              <i class="fa fa-times"></i>
            </button>
          </div>
        </div>
        <div class="block-content fs-sm">
          <p id="modalConfirmText">Are you sure?</p>
          <input type="hidden" name="clear_action" id="confirmClearAction" value="">
        </div>
        <div class="block-content block-content-full block-content-sm text-end border-top">
          <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-alt-primary">Yes, Proceed</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="modal" id="modal-confirm-delete" tabindex="-1" role="dialog" aria-labelledby="modal-confirm-delete" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="form-confirm-delete" method="POST" action="admin-logs.php" class="modal-content">
      <div class="block block-rounded shadow-none mb-0">
        <div class="block-header block-header-default">
          <h3 class="block-title" id="modalDeleteTitle">Confirm Delete</h3>
          <div class="block-options">
            <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
              <i class="fa fa-times"></i>
            </button>
          </div>
        </div>
        <div class="block-content fs-sm">
          <p id="modalDeleteText">Are you sure you want to delete this entry?</p>
          <input type="hidden" name="delete_action" id="confirmDeleteAction" value="">
          <input type="hidden" name="delete_id" id="confirmDeleteId" value="">
        </div>
        <div class="block-content block-content-full block-content-sm text-end border-top">
          <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-alt-primary">Yes, Delete</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Help Modal (provided “modal-normal” template) -->
<div class="modal" id="modal-normal" tabindex="-1" role="dialog" aria-labelledby="modal-normal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="block block-rounded shadow-none mb-0">
        <div class="block-header block-header-default">
          <h3 class="block-title">Logs &amp; Audit Help</h3>
          <div class="block-options">
            <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
              <i class="fa fa-times"></i>
            </button>
          </div>
        </div>
        <div class="block-content fs-sm">
          <p>This page aggregates all system logs for review and troubleshooting. You can:</p>
          <ul class="ps-3">
            <li>View or filter recent entries per category (Audit, Login, Registration, Sessions).</li>
            <li>Expand “Details” or “User Agent” popovers to inspect JSON payloads or agent strings.</li>
            <li>Export any log category to CSV for offline analysis.</li>
            <li>Delete individual entries or clear all entries in any category.</li>
            <li>Note that clearing logs is irreversible.</li>
          </ul>
        </div>
        <div class="block-content block-content-full block-content-sm text-end border-top">
          <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../inc/_global/views/page_end.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/footer_start.php'; ?>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof bootstrap === 'undefined') {
      console.error('Bootstrap 5 JS is not loaded.');
    }

    // Initialize all popovers (with container="body")
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (el) {
      new bootstrap.Popover(el);
    });
  });

  // Setup Confirm Clear Modal
  var clearModal = document.getElementById('modal-confirm-clear');
  clearModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var action = button.getAttribute('data-action');
    var label  = button.getAttribute('data-label');
    document.getElementById('modalConfirmTitle').textContent = 'Confirm Clear';
    document.getElementById('modalConfirmText').textContent  = 'Are you sure you want to clear all ' + label + '?';
    document.getElementById('confirmClearAction').value     = action;
  });

  // Setup Confirm Delete Modal
  var deleteModal = document.getElementById('modal-confirm-delete');
  deleteModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var action = button.getAttribute('data-action');
    var id     = button.getAttribute('data-id');
    var label  = button.getAttribute('data-label');
    document.getElementById('modalDeleteTitle').textContent = 'Confirm Delete';
    document.getElementById('modalDeleteText').textContent  = 'Are you sure you want to delete ' + label + '?';
    document.getElementById('confirmDeleteAction').value   = action;
    document.getElementById('confirmDeleteId').value       = id;
  });
</script>

<?php require __DIR__ . '/../inc/_global/views/footer_end.php'; ?>
