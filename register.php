<?php
// register.php

require 'inc/_global/config.php';
require 'inc/_global/smtp_mailer.php';
require_once __DIR__ . '/inc/_global/convert_timezone.php';
session_start();

// ──────────────────────────────────────────────────────────────────────────────
// 1) CAPTURE referrer code FROM GET (initial visit) OR POST (form submit)
// ──────────────────────────────────────────────────────────────────────────────
$incomingReferralCode = null;
$referrerId          = null;

// 1a) If the user first arrived via "?ref=CODE", capture that temporarily.
if (isset($_GET['ref']) && is_string($_GET['ref']) && trim($_GET['ref']) !== '') {
    $incomingReferralCode = trim($_GET['ref']);
}

// 1b) If the form is POSTed, the hidden input “ref” will carry the code forward.
//     Override $incomingReferralCode if $_POST['ref'] is set.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ref']) && trim($_POST['ref']) !== '') {
    $incomingReferralCode = trim($_POST['ref']);
}

// 1c) Now that $incomingReferralCode is known (from GET or POST), look up the user ID.
if ($incomingReferralCode !== null) {
    $refLookup = $pdo->prepare("
        SELECT id
        FROM `demon_users`
        WHERE referral_code = :code
          AND is_active = 1
        LIMIT 1
    ");
    $refLookup->execute(['code' => $incomingReferralCode]);
    $foundReferrer = $refLookup->fetch(PDO::FETCH_ASSOC);

    if ($foundReferrer) {
        $referrerId = (int)$foundReferrer['id'];
    }
}

function dbFormatToUK(string $utcDateTime): string
{
    // Assume your database timestamps are stored in UTC
    $dt = new DateTime($utcDateTime, new DateTimeZone('UTC'));
    // Change to UK timezone
    $dt->setTimezone(new DateTimeZone('Europe/London'));
    // Return in the usual “Y-m-d H:i:s” format (or change format if you prefer)
    return $dt->format('Y-m-d H:i:s');
}

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
function insert_audit_log(PDO $pdo, int $userId, string $action, array $detailsArray, string $ip) {
    $logSql = "
        INSERT INTO `demon_audit_logs`
        (user_id, action, details, ip_address, created_at)
        VALUES
        (:uid, :action, :details, :ip, NOW())
    ";
    $logStmt = $pdo->prepare($logSql);
    $logStmt->execute([
        'uid'     => $userId,
        'action'  => $action,
        'details' => json_encode($detailsArray),
        'ip'      => $ip
    ]);
}

$errors       = [];
$fieldErrors  = ['username' => '', 'email' => '', 'password' => '', 'password_confirm' => '', 'terms' => '', 'captcha' => ''];
$oldInput     = ['signup-username' => '', 'signup-email' => ''];
$showForm     = true;

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
    // For v2 we only need to check "success"
    return isset($json['success']) && $json['success'] === true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid submission. Please try again.';
    }

    // Collect old input
    $oldInput['signup-username'] = trim($_POST['signup-username'] ?? '');
    $oldInput['signup-email']    = trim($_POST['signup-email'] ?? '');

    $username        = $oldInput['signup-username'];
    $email           = $oldInput['signup-email'];
    $password        = $_POST['signup-password'] ?? '';
    $passwordConfirm = $_POST['signup-password-confirm'] ?? '';
    $agreeTerms      = isset($_POST['signup-terms']) && $_POST['signup-terms'] === '1';
    $recaptchaToken  = $_POST['g-recaptcha-response'] ?? '';

    // 1) Check if registration is allowed
    if (!$site['allow_registration']) {
        $errors[] = 'Registration is currently disabled.';
    }

    // 2a) Basic validation
    if ($username === '') {
        $fieldErrors['username'] = 'Username is required.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $fieldErrors['username'] = 'Username must be 3–50 characters and contain only letters, numbers, or underscores.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['email'] = 'A valid email address is required.';
    }

    if (strlen($password) < (int)$site['password_min_length']) {
        $fieldErrors['password'] = 'Password must be at least ' . (int)$site['password_min_length'] . ' characters.';
    }

    if ($password !== $passwordConfirm) {
        $fieldErrors['password_confirm'] = 'Passwords do not match.';
    }

    if (!$agreeTerms) {
        $fieldErrors['terms'] = 'You must agree to the terms and conditions.';
    }

    // 2b) Verify reCAPTCHA
    if ($site['captcha_site_key'] && $site['captcha_secret_key']) {
        if ($recaptchaToken === '' || !verify_recaptcha($recaptchaToken, $site['captcha_secret_key'])) {
            $fieldErrors['captcha'] = 'reCAPTCHA verification failed.';
        }
    }

    // 2c) Check uniqueness of username/email
    if (empty(array_filter($fieldErrors))) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `demon_users` WHERE username = :u");
        $stmt->execute(['u' => $username]);
        if ($stmt->fetchColumn() > 0) {
            $fieldErrors['username'] = 'Username is already taken.';
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `demon_users` WHERE email = :e");
        $stmt->execute(['e' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $fieldErrors['email'] = 'Email is already registered.';
        }
    }

    // 2d) If no field-specific errors, proceed
    if (empty($errors) && empty(array_filter($fieldErrors))) {
        // Always assign role_id = 7 (Awaiting Activation) on registration
        $defaultRoleId = 7;

        // Generate activation token and expiry
        $activationCode = '';
        for ($i = 0; $i < 6; $i++) {
            $activationCode .= (string) random_int(1, 9);
        }
        $activationExpiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            // ← Only one transaction begins here:
            $pdo->beginTransaction();

            // 3) INSERT INTO demon_users INCLUDING referrer_id
            $insertSql = "
                INSERT INTO `demon_users`
                (username, email, password_hash, role_id, is_active, status,
                 activation_token, activation_expires, created_at, updated_at,
                 referrer_id)
                VALUES
                (:username, :email, :ph, :role, 0, 'pending', :token, :expires, NOW(), NOW(),
                 :referrer_id)
            ";
            $stmt = $pdo->prepare($insertSql);
            $stmt->execute([
                'username'    => $username,
                'email'       => $email,
                'ph'          => $passwordHash,
                'role'        => $defaultRoleId,
                'token'       => $activationCode,
                'expires'     => $activationExpiry,
                'referrer_id' => $referrerId // may be null
            ]);
            $newUserId = (int)$pdo->lastInsertId();

            // Log registration in demon_registration_logs
            $regLog = $pdo->prepare("
                INSERT INTO `demon_registration_logs`
                (ip_address, email, user_agent, referrer, created_at)
                VALUES (:ip, :email, :ua, :ref, NOW())
            ");
            $regLog->execute([
                'ip'    => $_SERVER['REMOTE_ADDR'] ?? '',
                'email' => $email,
                'ua'    => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ref'   => $_SERVER['HTTP_REFERER'] ?? null
            ]);

            // Insert empty profile row
            $stmt = $pdo->prepare("
                INSERT INTO `demon_user_profiles` (user_id, created_at, updated_at)
                VALUES (:uid, NOW(), NOW())
            ");
            $stmt->execute(['uid' => $newUserId]);

            // Link user to role
            $stmt = $pdo->prepare("
                INSERT INTO `demon_user_roles` (user_id, role_id)
                VALUES (:uid, :rid)
            ");
            $stmt->execute([
                'uid' => $newUserId,
                'rid' => $defaultRoleId
            ]);

            // Insert welcome notification
            $stmt = $pdo->prepare("
                INSERT INTO `demon_notifications` (user_id, message, link, created_at)
                VALUES (:uid, :msg, 'dashboard.php', NOW())
            ");
            $welcomeMsg = "Welcome to " . addslashes($site['site_name']) . "! Let’s get started.";
            $stmt->execute([
                'uid' => $newUserId,
                'msg' => $welcomeMsg
            ]);

            // Audit log: user registration
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
            insert_audit_log(
                $pdo,
                $newUserId,
                'user.register',
                ['username' => $username, 'email' => $email],
                $ipAddress
            );

            // 4) GENERATE & STORE A UNIQUE referral_code FOR THIS NEW USER
            function generateReferralCode(): string {
                global $pdo;
                do {
                    $raw = strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
                    $check = $pdo->prepare("
                        SELECT COUNT(*) FROM demon_users WHERE referral_code = :code
                    ");
                    $check->execute(['code' => $raw]);
                    $exists = (int)$check->fetchColumn();
                } while ($exists > 0);
                return $raw;
            }

            $newReferralCode = generateReferralCode();
            $upd = $pdo->prepare("
                UPDATE `demon_users`
                SET referral_code = :code
                WHERE id = :uid
            ");
            $upd->execute([
                'code' => $newReferralCode,
                'uid'  => $newUserId
            ]);

            // 5) IF A VALID referrer_id EXISTS, INSERT INTO demon_referrals
            if ($referrerId !== null) {
                $insertRef = $pdo->prepare("
                    INSERT INTO `demon_referrals`
                      (referrer_id, referred_user_id, referral_code, invited_email, status, created_at)
                    VALUES
                      (:referrer, :referred, :code, :email, 'registered', NOW())
                ");
                $insertRef->execute([
                    'referrer' => $referrerId,
                    'referred' => $newUserId,
                    'code'     => $incomingReferralCode,
                    'email'    => $email
                ]);

            }

            // ← Only one commit() at the very end:
            $pdo->commit();
        } catch (\Exception $e) {
            // If any step failed, roll back the single transaction
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'An internal error occurred. Please try again later.';
        }

        if (empty($errors)) {
            // Send activation email
            $subject = "Your activation code for " . $site['site_name'];
            $body    = "
Hello $username,

Thank you for registering at {$site['site_name']}.

Your activation code is:

{$activationCode}

This code will expire in 5 minutes. Enter it at:

{$site['site_url']}/activate.php?email=" . urlencode($email) . "

If you did not sign up, please ignore this email.

Regards,
{$site['site_name']} Team
";
            $sent = send_smtp_mail($email, $username, $subject, $body, $site);
            if ($sent !== true) {
                $errors[] = 'Unable to send activation email: ' . htmlspecialchars($sent);
            } else {
                header('Location: activate.php?email=' . urlencode($email));
                exit;
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
          <h1 class="h3 fw-bold mt-4 mb-1">Create New Account</h1>
          <h2 class="fs-5 lh-base fw-normal text-muted mb-0">
            <?= htmlspecialchars($site['site_tagline']); ?>
          </h2>
        </div>
        <!-- END Header -->

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <?php foreach ($errors as $e): ?>
              <div><?= htmlspecialchars($e); ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Sign Up Form -->
        <?php if ($site['allow_registration']): ?>
          <form
            class="js-validation-signup"
            action="register.php"
            method="POST"
            novalidate
          >
            <input type="hidden" name="ref" value="<?= htmlspecialchars($incomingReferralCode); ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <div class="block block-themed block-rounded block-fx-shadow">
              <div class="block-header bg-gd-emerald">
                <h3 class="block-title">Please add your details</h3>
              </div>
              <div class="block-content">
                <div class="form-floating mb-4">
                  <input
                    type="text"
                    class="form-control <?= $fieldErrors['username'] ? 'is-invalid' : '' ?>"
                    id="signup-username"
                    name="signup-username"
                    placeholder="Enter your username"
                    value="<?= htmlspecialchars($oldInput['signup-username']); ?>"
                    required
                  >
                  <label class="form-label" for="signup-username">Username</label>
                  <?php if ($fieldErrors['username']): ?>
                    <div class="invalid-feedback">
                      <?= htmlspecialchars($fieldErrors['username']); ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="form-floating mb-4">
                  <input
                    type="email"
                    class="form-control <?= $fieldErrors['email'] ? 'is-invalid' : '' ?>"
                    id="signup-email"
                    name="signup-email"
                    placeholder="Enter your email"
                    value="<?= htmlspecialchars($oldInput['signup-email']); ?>"
                    required
                  >
                  <label class="form-label" for="signup-email">Email</label>
                  <?php if ($fieldErrors['email']): ?>
                    <div class="invalid-feedback">
                      <?= htmlspecialchars($fieldErrors['email']); ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="form-floating mb-4">
                  <input
                    type="password"
                    class="form-control <?= $fieldErrors['password'] ? 'is-invalid' : '' ?>"
                    id="signup-password"
                    name="signup-password"
                    placeholder="Enter your password"
                    required
                  >
                  <label class="form-label" for="signup-password">Password</label>
                  <?php if ($fieldErrors['password']): ?>
                    <div class="invalid-feedback">
                      <?= htmlspecialchars($fieldErrors['password']); ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="form-floating mb-4">
                  <input
                    type="password"
                    class="form-control <?= $fieldErrors['password_confirm'] ? 'is-invalid' : '' ?>"
                    id="signup-password-confirm"
                    name="signup-password-confirm"
                    placeholder="Confirm password"
                    required
                  >
                  <label class="form-label" for="signup-password-confirm">Confirm Password</label>
                  <?php if ($fieldErrors['password_confirm']): ?>
                    <div class="invalid-feedback">
                      <?= htmlspecialchars($fieldErrors['password_confirm']); ?>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="mb-4">
                  <div class="form-check <?= $fieldErrors['terms'] ? 'is-invalid' : '' ?>">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      id="signup-terms"
                      name="signup-terms"
                      value="1"
                      <?= isset($_POST['signup-terms']) ? 'checked' : '' ?>
                    >
                    <label class="form-check-label" for="signup-terms">
                      I agree to
                      <a href="<?= htmlspecialchars($site['terms_of_service_url']); ?>" target="_blank">
                        Terms &amp; Conditions
                      </a>
                    </label>
                    <?php if ($fieldErrors['terms']): ?>
                      <div class="invalid-feedback d-block">
                        <?= htmlspecialchars($fieldErrors['terms']); ?>
                      </div>
                    <?php endif; ?>
                  </div>
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

                <div class="text-center mb-4">
                  <button type="submit" class="btn btn-lg btn-alt-primary fw-semibold">
                    Create Account
                  </button>
                </div>
              </div>
              <div class="block-content block-content-full bg-body-light d-flex justify-content-between">
                <a class="fs-sm fw-medium link-fx text-muted me-2 mb-1 d-inline-block" href="login.php">
                  <i class="fa fa-arrow-left opacity-50 me-1"></i> Sign In
                </a>
                <a class="fs-sm fw-medium link-fx text-muted me-2 mb-1 d-inline-block"
                   href="<?= htmlspecialchars($site['terms_of_service_url']); ?>" target="_blank">
                  <i class="fa fa-book opacity-50 me-1"></i> Read Terms
                </a>
              </div>
            </div>
          </form>
        <?php else: ?>
          <div class="alert alert-warning text-center">
            Registration is currently disabled.
          </div>
        <?php endif; ?>
        <!-- END Sign Up Form -->
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
<?php $cb->get_js('js/pages/op_auth_signup.min.js'); ?>

<?php require 'inc/_global/views/footer_end.php'; ?>
