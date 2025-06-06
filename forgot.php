<?php
// forgot.php

require 'inc/_global/config.php';
require 'inc/_global/smtp_mailer.php';
session_start();

// Set PHP timezone to site setting, but only if it’s non‐empty and valid:
$tz = trim($site['timezone'] ?? '');
if ($tz !== '' && in_array($tz, timezone_identifiers_list(), true)) {
    date_default_timezone_set($tz);
} else {
    // fallback to UTC (or pick any default you like):
    date_default_timezone_set('UTC');
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
        'uid'     => $userId,
        'action'  => $action,
        'details' => json_encode($detailsArray),
        'ip'      => $ip
    ]);
}

$errors       = [];
$fieldErrors  = ['credential' => '', 'csrf' => '', 'captcha' => ''];
$oldInput     = ['reminder-credential' => ''];
$success      = false;

// Helper: generate a random 12-character password (letters + digits)
function generate_random_password(int $length = 12): string {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $pw = '';
    for ($i = 0; $i < $length; $i++) {
        $pw .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $pw;
}

// Helper: verify reCAPTCHA response
function verify_recaptcha($token, $secret) {
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
    $context  = stream_context_create($options);
    $result   = file_get_contents($url, false, $context);
    if ($result === false) {
        return false;
    }
    $json = json_decode($result, true);
    return isset($json['success']) && $json['success'] === true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $fieldErrors['csrf'] = 'Invalid form submission.';
    }

    // Collect old input
    $oldInput['reminder-credential'] = trim($_POST['reminder-credential'] ?? '');

    $credential      = $oldInput['reminder-credential'];
    $recaptchaToken  = $_POST['g-recaptcha-response'] ?? '';
    $ipAddress       = $_SERVER['REMOTE_ADDR'] ?? '';

    // 1) Basic validation
    if ($credential === '') {
        $fieldErrors['credential'] = 'Please enter your username or email.';
    }

    // 2) Verify reCAPTCHA if configured
    if (empty(array_filter($fieldErrors)) && $site['captcha_site_key'] && $site['captcha_secret_key']) {
        if ($recaptchaToken === '' || !verify_recaptcha($recaptchaToken, $site['captcha_secret_key'])) {
            $fieldErrors['captcha'] = 'reCAPTCHA verification failed.';
        }
    }

    if (empty(array_filter($fieldErrors))) {
        // 3) Find user by username OR email
        $stmt = $pdo->prepare("
            SELECT id, username, email, is_active
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
            $fieldErrors['credential'] = 'No account found with that username or email.';
            insert_audit_log(
                $pdo,
                null,
                'user.password_reset_failed',
                ['credential' => $credential, 'reason' => 'not_found'],
                $ipAddress
            );
        } elseif ((int)$user['is_active'] !== 1) {
            $fieldErrors['credential'] = 'Account is not active. Please activate your account first.';
            insert_audit_log(
                $pdo,
                (int)$user['id'],
                'user.password_reset_failed',
                ['user_id' => $user['id'], 'reason' => 'inactive'],
                $ipAddress
            );
        } else {
            // 4) Generate a new random password and hash it
            $newPassword = generate_random_password(12);
            $newHash     = password_hash($newPassword, PASSWORD_DEFAULT);

            try {
                $pdo->beginTransaction();

                // Update the user's password in `demon_users`
                $update = $pdo->prepare("
                    UPDATE `demon_users`
                    SET password_hash = :ph, updated_at = NOW()
                    WHERE id = :uid
                ");
                $update->execute([
                    'ph'  => $newHash,
                    'uid' => $user['id']
                ]);

                // Insert into demon_password_history
                $hist = $pdo->prepare("
                    INSERT INTO `demon_password_history`
                    (user_id, password_hash, created_at)
                    VALUES (:uid, :ph, NOW())
                ");
                $hist->execute([
                    'uid' => $user['id'],
                    'ph'  => $newHash
                ]);

                // Add a notification prompting a password change
                $notif = $pdo->prepare("
                    INSERT INTO `demon_notifications`
                    (user_id, message, link, created_at)
                    VALUES (:uid, :msg, :link, NOW())
                ");
                $notifMsg  = "Your password was reset. For security, please change it now.";
                $notifLink = "profile.php";
                $notif->execute([
                    'uid'  => $user['id'],
                    'msg'  => $notifMsg,
                    'link' => $notifLink
                ]);

                $pdo->commit();
            } catch (\Exception $e) {
                $pdo->rollBack();
                $errors[] = 'An internal error occurred. Please try again later.';
                insert_audit_log(
                    $pdo,
                    (int)$user['id'],
                    'user.password_reset_error',
                    ['user_id' => $user['id'], 'error' => $e->getMessage()],
                    $ipAddress
                );
            }

            if (empty($errors)) {
                // Audit log: successful password reset
                insert_audit_log(
                    $pdo,
                    (int)$user['id'],
                    'user.password_reset',
                    ['user_id' => $user['id']],
                    $ipAddress
                );

                // Send the new password via email
                $subject = "Your new password for " . $site['site_name'];
                $body    = "
Hello {$user['username']},

You requested a password reset for your account on {$site['site_name']}.
Your new password is:

{$newPassword}

Please log in at {$site['site_url']}/login.php and change your password immediately.

If you did not request this, please contact support.

Regards,
{$site['site_name']} Team
";
                $result = send_smtp_mail($user['email'], $user['username'], $subject, $body, $site);
                if ($result !== true) {
                    $errors[] = 'Unable to send new password email: ' . htmlspecialchars($result);
                    insert_audit_log(
                        $pdo,
                        (int)$user['id'],
                        'user.password_reset_email_error',
                        ['user_id' => $user['id'], 'error' => $result],
                        $ipAddress
                    );
                } else {
                    $success = true;
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
          <h1 class="h3 fw-bold mt-4 mb-1">Don’t worry, we’ve got your back</h1>
          <h2 class="fs-5 lh-base fw-normal text-muted mb-0">
            Please enter your username or email
          </h2>
        </div>
        <!-- END Header -->

        <?php if ($fieldErrors['csrf']): ?>
          <div class="alert alert-danger">
            <?= htmlspecialchars($fieldErrors['csrf']); ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <?php foreach ($errors as $e): ?>
              <div><?= htmlspecialchars($e); ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="alert alert-success text-center">
            A new password has been emailed to you.
          </div>
          <div class="text-center mt-3">
            <a href="login.php" class="btn btn-alt-secondary">
              Back to Login
            </a>
          </div>
        <?php else: ?>
          <!-- Reminder Form -->
          <form class="js-validation-reminder" action="forgot.php" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <div class="block block-themed block-rounded block-fx-shadow text-center">
              <div class="block-header bg-gd-primary">
                <h3 class="block-title">Password Reminder</h3>
              </div>
              <div class="block-content">
                <div class="form-floating mb-4">
                  <input
                    type="text"
                    class="form-control <?= $fieldErrors['credential'] ? 'is-invalid' : '' ?>"
                    id="reminder-credential"
                    name="reminder-credential"
                    placeholder="Enter your email or username"
                    value="<?= htmlspecialchars($oldInput['reminder-credential']); ?>"
                    required
                  >
                  <label class="form-label" for="reminder-credential">
                    Username or Email
                  </label>
                  <?php if ($fieldErrors['credential']): ?>
                    <div class="invalid-feedback">
                      <?= htmlspecialchars($fieldErrors['credential']); ?>
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

                <div class="mb-4">
                  <button type="submit" class="btn btn-lg btn-alt-primary fw-semibold">
                    Reset Password
                  </button>
                </div>
              </div>
              <div class="block-content block-content-full bg-body-light mb-4">
                <a class="fs-sm fw-medium link-fx text-muted me-2 mb-1 d-inline-block" href="login.php">
                  <i class="fa fa-arrow-left opacity-50 me-1"></i> Sign In
                </a>
              </div>
            </div>
          </form>
          <!-- END Reminder Form -->
        <?php endif; ?>
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
<?php $cb->get_js('js/pages/op_auth_reminder.min.js'); ?>

<?php require 'inc/_global/views/footer_end.php'; ?>
