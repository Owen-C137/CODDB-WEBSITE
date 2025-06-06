<?php
// admin/admin-user-add.php

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

// Fetch all roles for the dropdown
$roleStmt = $pdo->query("SELECT id, name FROM `demon_roles` ORDER BY level DESC, name ASC");
$roles = $roleStmt->fetchAll();

// Initialize variables
$errors      = [];
$oldInput    = ['username' => '', 'email' => '', 'role_id' => ''];
$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect old input
    $oldInput['username'] = trim($_POST['username'] ?? '');
    $oldInput['email']    = trim($_POST['email'] ?? '');
    $oldInput['role_id']  = $_POST['role_id'] ?? '';

    $username        = $oldInput['username'];
    $email           = $oldInput['email'];
    $password        = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $roleId          = (int)$oldInput['role_id'];

    // 1) Basic validation
    if ($username === '') {
        $errors[] = 'Username is required.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $errors[] = 'Username must be 3–50 characters and contain only letters, numbers, or underscores.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $passwordConfirm) {
        $errors[] = 'Passwords do not match.';
    }

    // Check that selected role exists
    $roleExists = false;
    foreach ($roles as $r) {
        if ((int)$r['id'] === $roleId) {
            $roleExists = true;
            break;
        }
    }
    if (!$roleExists) {
        $errors[] = 'Please select a valid user group.';
    }

    // 2) Check uniqueness of username/email
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `demon_users` WHERE username = :u");
        $stmt->execute(['u' => $username]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Username is already taken.';
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `demon_users` WHERE email = :e");
        $stmt->execute(['e' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Email is already registered.';
        }
    }

    // 3) If no errors, insert new user
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $adminId = (int)($_SESSION['user_id'] ?? 0);
        $ip      = $_SERVER['REMOTE_ADDR'] ?? '';

        try {
            $pdo->beginTransaction();

            // Insert into demon_users
            $insertSql = "
                INSERT INTO `demon_users`
                (username, email, password_hash, role_id, is_active, status, created_at, updated_at)
                VALUES
                (:username, :email, :ph, :role, 1, 'active', NOW(), NOW())
            ";
            $stmt = $pdo->prepare($insertSql);
            $stmt->execute([
                'username' => $username,
                'email'    => $email,
                'ph'       => $passwordHash,
                'role'     => $roleId
            ]);
            $newUserId = (int)$pdo->lastInsertId();

            // Insert empty profile row
            $stmt = $pdo->prepare("
                INSERT INTO `demon_user_profiles` (user_id, created_at, updated_at)
                VALUES (:uid, NOW(), NOW())
            ");
            $stmt->execute(['uid' => $newUserId]);

            // Insert initial notification
            $stmt = $pdo->prepare("
                INSERT INTO `demon_notifications` (user_id, message, link, created_at)
                VALUES (:uid, :msg, 'dashboard.php', NOW())
            ");
            $welcomeMsg = "Your account has been created by an administrator.";
            $stmt->execute([
                'uid' => $newUserId,
                'msg' => $welcomeMsg
            ]);

            // Audit log
            insert_audit_log(
                $pdo,
                $adminId,
                'user.create',
                ['new_user_id' => $newUserId, 'username' => $username, 'email' => $email, 'role_id' => $roleId],
                $ip
            );

            $pdo->commit();
            $_SESSION['flash_success'] = "User '{$username}' created successfully.";
            header('Location: admin-user-add.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'An internal error occurred. Please try again later.';
            // Log the failure
            insert_audit_log(
                $pdo,
                $adminId,
                'user.create_error',
                ['error' => $e->getMessage(), 'username' => $username, 'email' => $email, 'role_id' => $roleId],
                $ip
            );
        }
    }
}
?>

<?php
$cb->inc_header  = __DIR__ . '/../inc/backend/views/inc_header.php';
$cb->inc_sidebar = __DIR__ . '/../inc/backend/views/inc_sidebar.php';
$cb->inc_footer  = __DIR__ . '/../inc/backend/views/inc_footer.php';

// Codebase – Page specific configuration
$cb->l_m_content = 'narrow';
?>

<?php require __DIR__ . '/../inc/_global/views/head_start.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/head_end.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/page_start.php'; ?>

<!-- Page Content -->
<div class="content">
  <h2 class="content-heading">Create New User</h2>

  <!-- Flash Messages -->
  <?php if ($flashSuccess): ?>
    <div class="alert alert-success">
      <?= htmlspecialchars($flashSuccess); ?>
    </div>
  <?php endif; ?>
  <?php if ($flashError): ?>
    <div class="alert alert-danger">
      <?= htmlspecialchars($flashError); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form action="admin-user-add.php" method="POST" novalidate>
    <div class="block block-rounded block-themed">
      <div class="block-header bg-primary">
        <h3 class="block-title">User Details</h3>
      </div>
      <div class="block-content">

        <div class="row mb-3">
          <div class="col-md-6">
            <label for="username" class="form-label">Username *</label>
            <input type="text"
                   class="form-control"
                   id="username"
                   name="username"
                   placeholder="Enter username"
                   value="<?= htmlspecialchars($oldInput['username']); ?>"
                   required>
          </div>
          <div class="col-md-6">
            <label for="email" class="form-label">Email Address *</label>
            <input type="email"
                   class="form-control"
                   id="email"
                   name="email"
                   placeholder="Enter email"
                   value="<?= htmlspecialchars($oldInput['email']); ?>"
                   required>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label for="password" class="form-label">Password *</label>
            <input type="password"
                   class="form-control"
                   id="password"
                   name="password"
                   placeholder="Enter password"
                   required>
          </div>
          <div class="col-md-6">
            <label for="password_confirm" class="form-label">Confirm Password *</label>
            <input type="password"
                   class="form-control"
                   id="password_confirm"
                   name="password_confirm"
                   placeholder="Confirm password"
                   required>
          </div>
        </div>

        <div class="mb-3">
          <label for="role_id" class="form-label">User Group *</label>
          <select class="form-select"
                  id="role_id"
                  name="role_id"
                  required>
            <option value="">— Select Group —</option>
            <?php foreach ($roles as $r): ?>
              <option value="<?= $r['id']; ?>"
                <?= ((string)$r['id'] === (string)$oldInput['role_id']) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($r['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="text-end">
          <button type="submit" class="btn btn-alt-primary">
            <i class="fa fa-plus-circle me-1"></i> Create User
          </button>
        </div>
      </div>
    </div>
  </form>
</div>
<!-- END Page Content -->

<?php require __DIR__ . '/../inc/_global/views/page_end.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/footer_start.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/footer_end.php'; ?>
