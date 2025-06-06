<?php
// login.php

require 'inc/_global/config.php';
session_start();

// If site timezone is set, use it; otherwise default to UTC
$tz = $site['timezone'] ?? '';
if ($tz && in_array($tz, timezone_identifiers_list())) {
    date_default_timezone_set($tz);
} else {
    date_default_timezone_set('UTC');
}

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Helper: insert an audit log entry
function insert_audit_log(PDO $pdo, ?int $userId, string $action, array $detailsArray, string $ip) {
    $logSql = "
        INSERT INTO `demon_audit_logs`
        (user_id, action, details, ip_address, created_at)
        VALUES
        (:uid, :action, :details, :ip, NOW())
    ";
    $logStmt = $pdo->prepare($logSql);
    $logStmt->execute([
        'uid'     => $userId,             // null if login failed/no user
        'action'  => $action,
        'details' => json_encode($detailsArray),
        'ip'      => $ip
    ]);
}

$errors      = [];
$fieldErrors = ['credential' => '', 'password' => '', 'csrf' => '', 'captcha' => ''];
$oldInput    = ['login-credential' => ''];
$showForm    = true;

// Helper: verify reCAPTCHA response
function verify_recaptcha(string $token, string $secret): bool {
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret'   => $secret,
        'response' => $token
    ];
    $options = [
        'http' => [
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    $context = stream_context_create($options);
    $result  = file_get_contents($url, false, $context);
    if ($result === false) {
        return false;
    }
    $json = json_decode($result, true);
    return isset($json['success']) && $json['success'] === true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) CSRF check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $fieldErrors['csrf'] = 'Invalid form submission.';
    }

    // Collect old input
    $oldInput['login-credential'] = trim($_POST['login-credential'] ?? '');

    $credential     = $oldInput['login-credential'];
    $password       = $_POST['login-password'] ?? '';
    $rememberMe     = isset($_POST['login-remember-me']);
    $recaptchaToken = $_POST['g-recaptcha-response'] ?? '';
    $ipAddress      = $_SERVER['REMOTE_ADDR'] ?? '';

    // 2) Basic validation
    if ($credential === '') {
        $fieldErrors['credential'] = 'Username or email is required.';
    }
    if ($password === '') {
        $fieldErrors['password'] = 'Password is required.';
    }

    // 3) Verify reCAPTCHA if configured
    if (empty(array_filter($fieldErrors)) && $site['captcha_site_key'] && $site['captcha_secret_key']) {
        if ($recaptchaToken === '' || !verify_recaptcha($recaptchaToken, $site['captcha_secret_key'])) {
            $fieldErrors['captcha'] = 'reCAPTCHA verification failed.';
        }
    }

    if (empty(array_filter($fieldErrors))) {
        // 4) Lookup user by username OR email
        $stmt = $pdo->prepare("
            SELECT id, username, email, password_hash, is_active, status,
                   login_attempts, lockout_until, role_id
            FROM `demon_users`
            WHERE username = :cred_username
               OR email    = :cred_email
            LIMIT 1
        ");
        $stmt->execute([
            'cred_username' => $credential,
            'cred_email'    => $credential
        ]);
        $user = $stmt->fetch();

        if (!$user) {
            $fieldErrors['credential'] = 'Invalid credentials.';
            insert_audit_log(
                $pdo,
                null,
                'user.login_failed',
                ['credential' => $credential, 'reason' => 'no such user'],
                $ipAddress
            );
        } else {
            // 5) Check lockout
            if ($user['lockout_until'] !== null && strtotime($user['lockout_until']) > time()) {
                $remaining = strtotime($user['lockout_until']) - time();
                $minutes   = ceil($remaining / 60);
                $fieldErrors['credential'] = "Account locked. Try again in {$minutes} minute(s).";
                insert_audit_log(
                    $pdo,
                    $user['id'],
                    'user.login_failed',
                    ['reason' => 'locked_out', 'lockout_until' => $user['lockout_until']],
                    $ipAddress
                );
            } elseif ((int)$user['is_active'] !== 1) {
                $fieldErrors['credential'] = 'Account is not active. Please activate first.';
                insert_audit_log(
                    $pdo,
                    $user['id'],
                    'user.login_failed',
                    ['reason' => 'inactive', 'status' => $user['status']],
                    $ipAddress
                );
            } else {
                // 6) Verify password
                if (!password_verify($password, $user['password_hash'])) {
                    // Increment login_attempts
                    $newAttempts   = $user['login_attempts'] + 1;
                    $lockoutUntil  = null;
                    if ($newAttempts >= 5) {
                        // Lock for 15 minutes
                        $lockoutUntil = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                        $newAttempts  = 0; // reset after lockout
                    }
                    $upd = $pdo->prepare("
                        UPDATE `demon_users`
                        SET login_attempts = :la,
                            lockout_until = :lu
                        WHERE id = :uid
                    ");
                    $upd->execute([
                        'la'  => $newAttempts,
                        'lu'  => $lockoutUntil,
                        'uid' => $user['id']
                    ]);

                    $fieldErrors['credential'] = 'Invalid credentials.';
                    insert_audit_log(
                        $pdo,
                        $user['id'],
                        'user.login_failed',
                        ['reason' => 'wrong_password', 'attempts' => $newAttempts],
                        $ipAddress
                    );
                } else {
                    // ── SUCCESSFUL LOGIN! ─────────────────────────────────────────────────
                    // 7) Reset login_attempts, clear lockout, update last_login
                    $upd = $pdo->prepare("
                        UPDATE `demon_users`
                        SET login_attempts = 0,
                            lockout_until = NULL,
                            last_login    = NOW()
                        WHERE id = :uid
                    ");
                    $upd->execute(['uid' => $user['id']]);

                    // 8) Regenerate session and set basic session vars
                    session_regenerate_id(true);
                    $_SESSION['user_id']       = $user['id'];
                    $_SESSION['username']      = $user['username'];
                    $_SESSION['LAST_ACTIVITY'] = time();

                    // ── FETCH & STORE ROLE NAME IN SESSION ──────────────────────────────
                    $roleStmt = $pdo->prepare("
                        SELECT r.name
                        FROM `demon_roles` AS r
                        WHERE r.id = :rid
                        LIMIT 1
                    ");
                    $roleStmt->execute(['rid' => $user['role_id']]);
                    if ($roleRow = $roleStmt->fetch()) {
                        $_SESSION['role_name'] = $roleRow['name'];
                    } else {
                        $_SESSION['role_name'] = 'registered';
                    }
                    // ─────────────────────────────────────────────────────────────────────

                    // 9) Remember Me: set a long-lived cookie and DB session
                    if ($rememberMe) {
                        $token   = bin2hex(random_bytes(32));
                        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                        $ins = $pdo->prepare("
                            INSERT INTO `demon_user_sessions`
                            (user_id, session_token, ip_address, user_agent, created_at, expires_at)
                            VALUES (:uid, :token, :ip, :ua, NOW(), :exp)
                        ");
                        $ins->execute([
                            'uid'   => $user['id'],
                            'token' => $token,
                            'ip'    => $ipAddress,
                            'ua'    => $_SERVER['HTTP_USER_AGENT'] ?? '',
                            'exp'   => $expires
                        ]);
                        setcookie(
                            'remember_token',
                            $token,
                            time() + (86400 * 30),
                            '/',
                            '',
                            true,
                            true
                        );
                    }

                    // Audit log: successful login
                    insert_audit_log(
                        $pdo,
                        $user['id'],
                        'user.login',
                        ['username' => $user['username'], 'email' => $user['email']],
                        $ipAddress
                    );

                    header('Location: dashboard.php');
                    exit;
                }
            }
        }
    }
}
?>
<?php require 'inc/_global/views/head_start.php'; ?>
<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<!-- Page Content -->
<div class="bg-body-dark">
  <div class="hero-static content content-full px-1">
    <div class="row mx-0 justify-content-center">
      <div class="col-lg-8 col-xl-6">
        <!-- Header -->
        <div class="py-4 text-center">
          <?php if (!empty($site['logo_url'])): ?>
            <a href="<?= htmlspecialchars($site['site_url']); ?>">
              <img src="<?= htmlspecialchars($site['logo_url']); ?>"
                   alt="<?= htmlspecialchars($site['site_name']); ?>"
                   style="max-height: 80px;">
            </a>
          <?php else: ?>
            <a class="link-fx fw-bold" href="index.php">
              <span class="fs-4"><?= htmlspecialchars($site['site_name']); ?></span>
            </a>
          <?php endif; ?>
          <h1 class="h3 fw-bold mt-4 mb-1">Welcome back</h1>
          <h2 class="fs-5 lh-base fw-normal text-muted mb-0">
            Sign in to your account
          </h2>
        </div>
        <!-- END Header -->

        <?php if ($fieldErrors['csrf']): ?>
          <div class="alert alert-danger">
            <?= htmlspecialchars($fieldErrors['csrf']); ?>
          </div>
        <?php endif; ?>

        <!-- Sign In Form -->
        <form class="js-validation-signin" action="login.php" method="POST" novalidate>
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
          <div class="block block-themed block-rounded block-fx-shadow">
            <div class="block-header bg-gd-dusk">
              <h3 class="block-title">Please Sign In</h3>
            </div>
            <div class="block-content">
              <div class="form-floating mb-4">
                <input
                  type="text"
                  class="form-control <?= $fieldErrors['credential'] ? 'is-invalid' : '' ?>"
                  id="login-credential"
                  name="login-credential"
                  placeholder="Enter your username or email"
                  value="<?= htmlspecialchars($oldInput['login-credential']); ?>"
                  required
                >
                <label class="form-label" for="login-credential">Username or Email</label>
                <?php if ($fieldErrors['credential']): ?>
                  <div class="invalid-feedback">
                    <?= htmlspecialchars($fieldErrors['credential']); ?>
                  </div>
                <?php endif; ?>
              </div>
              <div class="form-floating mb-4">
                <input
                  type="password"
                  class="form-control <?= $fieldErrors['password'] ? 'is-invalid' : '' ?>"
                  id="login-password"
                  name="login-password"
                  placeholder="Enter your password"
                  required
                >
                <label class="form-label" for="login-password">Password</label>
                <?php if ($fieldErrors['password']): ?>
                  <div class="invalid-feedback">
                    <?= htmlspecialchars($fieldErrors['password']); ?>
                  </div>
                <?php endif; ?>
              </div>

              <?php if ($site['captcha_site_key'] && $site['captcha_secret_key']): ?>
                <div class="mb-4 <?= $fieldErrors['captcha'] ? 'is-invalid' : '' ?>">
                  <div class="g-recaptcha"
                       data-sitekey="<?= htmlspecialchars($site['captcha_site_key']); ?>">
                  </div>
                  <?php if ($fieldErrors['captcha']): ?>
                    <div class="invalid-feedback d-block">
                      <?= htmlspecialchars($fieldErrors['captcha']); ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>

              <div class="row">
                <div class="col-sm-6 d-sm-flex align-items-center push">
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      value="1"
                      id="login-remember-me"
                      name="login-remember-me"
                    >
                    <label class="form-check-label" for="login-remember-me">Remember Me</label>
                  </div>
                </div>
                <div class="col-sm-6 text-sm-end push">
                  <button type="submit" class="btn btn-lg btn-alt-primary fw-medium">
                    Sign In
                  </button>
                </div>
              </div>
            </div>
            <div class="block-content block-content-full bg-body-light text-center d-flex justify-content-between">
              <a class="fs-sm fw-medium link-fx text-muted me-2 mb-1 d-inline-block" href="register.php">
                <i class="fa fa-plus opacity-50 me-1"></i> Create Account
              </a>
              <a class="fs-sm fw-medium link-fx text-muted me-2 mb-1 d-inline-block" href="forgot.php">
                Forgot Password
              </a>
            </div>
          </div>
        </form>
        <!-- END Sign In Form -->
      </div>
    </div>
  </div>
</div>
<!-- END Page Content -->

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- reCAPTCHA script -->
<?php if ($site['captcha_site_key'] && $site['captcha_secret_key']): ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

<!-- jQuery (required for jQuery Validation plugins) -->
<?php $cb->get_js('js/lib/jquery.min.js'); ?>

<!-- Page JS Plugins -->
<?php $cb->get_js('js/plugins/jquery-validation/jquery.validate.min.js'); ?>

<!-- Page JS Code -->
<?php $cb->get_js('js/pages/op_auth_signin.min.js'); ?>

<?php require 'inc/_global/views/footer_end.php'; ?>
