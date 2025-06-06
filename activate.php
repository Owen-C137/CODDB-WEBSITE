<?php
// activate.php

require 'inc/_global/config.php';
require 'inc/_global/smtp_mailer.php';
session_start();

// If the user is already logged in, redirect to dashboard
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
        'uid'     => $userId,
        'action'  => $action,
        'details' => json_encode($detailsArray),
        'ip'      => $ip
    ]);
}

$errors    = [];
$success   = false;
$email     = $_GET['email'] ?? '';
$resend    = isset($_GET['resend']) && $_GET['resend'] == '1';
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';

// If no email provided, redirect to login
if ($email === '') {
    header('Location: login.php');
    exit;
}

// 0) Fetch user and check if already active
$stmt    = $pdo->prepare("SELECT id, is_active FROM `demon_users` WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$userRow = $stmt->fetch();

if (!$userRow) {
    // Email not found
    $errors[] = 'Email address not found.';
    insert_audit_log(
        $pdo,
        null,
        'user.activate_failed',
        ['email' => $email, 'reason' => 'not_found'],
        $ipAddress
    );
} elseif ((int)$userRow['is_active'] === 1) {
    // Already activated: show message and exit
    insert_audit_log(
        $pdo,
        $userRow['id'],
        'user.activate_skipped',
        ['email' => $email, 'reason' => 'already_active'],
        $ipAddress
    );
    require 'inc/_global/views/head_start.php';
    require 'inc/_global/views/head_end.php';
    require 'inc/_global/views/page_start.php';
    ?>
    <div class="bg-body-dark">
      <div class="hero-static content content-full px-1">
        <div class="row mx-0 justify-content-center">
          <div class="col-lg-8 col-xl-6">
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
              <div class="mb-4 mt-5">
                <i class="si si-info fa-3x"></i>
              </div>
              <h1 class="h3 fw-bold mb-1">Account Already Activated</h1>
              <h2 class="fs-5 lh-base fw-normal text-muted mb-4">
                Your account is already active. You can log in below.
              </h2>
              <a href="login.php" class="btn btn-lg btn-alt-primary">
                Go to Login
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
    require 'inc/_global/views/page_end.php';
    require 'inc/_global/views/footer_start.php';
    require 'inc/_global/views/footer_end.php';
    exit;
}

// 1) Handle “Resend code” only if &resend=1
if ($resend && empty($errors)) {
    // Generate a fresh 6-digit code (expires in 5 minutes)
    $newCode   = (string) random_int(100000, 999999);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Update activation_token & activation_expires
    $upd = $pdo->prepare("
        UPDATE `demon_users`
        SET activation_token = :code,
            activation_expires = :expires,
            updated_at = NOW()
        WHERE id = :uid
    ");
    $upd->execute([
        ':code'    => $newCode,
        ':expires' => $expiresAt,
        ':uid'     => $userRow['id']
    ]);

    // Send new code via SMTP
    $subject = "Your activation code for " . $site['site_name'];
    $body    = "
Hello,

As requested, here is your new activation code for {$site['site_name']}:

{$newCode}

This code will expire in 5 minutes. Enter it at:

{$site['site_url']}/activate.php?email=" . urlencode($email) . "

If you did not request this, please ignore.

Regards,
{$site['site_name']} Team
";
    $sent = send_smtp_mail($email, '', $subject, $body, $site);
    if ($sent !== true) {
        $errors[] = 'Unable to send activation email: ' . htmlspecialchars($sent);
        insert_audit_log(
            $pdo,
            $userRow['id'],
            'user.activate_resend_error',
            ['user_id' => $userRow['id'], 'email' => $email, 'error' => $sent],
            $ipAddress
        );
    } else {
        $success = 'A new activation code has been sent to your email.';
        insert_audit_log(
            $pdo,
            $userRow['id'],
            'user.activate_resend',
            ['user_id' => $userRow['id'], 'email' => $email],
            $ipAddress
        );
    }
}

// 2) Handle form submission (POST) to verify the 6-digit code
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Grab email from hidden field
    $email = trim($_POST['email'] ?? '');

    // Re-fetch user to get latest activation_expires
    $stmt = $pdo->prepare("SELECT id, referrer_id, activation_expires FROM `demon_users` WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        $errors[] = 'Email address not found.';
        insert_audit_log(
            $pdo,
            null,
            'user.activate_failed',
            ['email' => $email, 'reason' => 'not_found_post'],
            $ipAddress
        );
    } else {
        // Combine six inputs into one code string
        $code = '';
        for ($i = 1; $i <= 6; $i++) {
            $part = $_POST["num$i"] ?? '';
            if (!ctype_digit($part) || strlen($part) !== 1) {
                $errors[] = 'Invalid code format.';
                break;
            }
            $code .= $part;
        }

        if (empty($errors)) {
            // Lookup user by email AND activation_token
            $stmt = $pdo->prepare("
                SELECT id, activation_expires
                FROM `demon_users`
                WHERE email = :email
                  AND activation_token = :code
                LIMIT 1
            ");
            $stmt->execute([
                ':email' => $email,
                ':code'  => $code
            ]);
            $row = $stmt->fetch();

            if (!$row) {
                $errors[] = 'Invalid or expired code.';
                insert_audit_log(
                    $pdo,
                    $user['id'],
                    'user.activate_failed',
                    ['user_id' => $user['id'], 'reason' => 'invalid_code', 'code_entered' => $code],
                    $ipAddress
                );
            } elseif (strtotime($row['activation_expires']) < time()) {
                $errors[] = 'That code has expired. Please request a new one.';
                insert_audit_log(
                    $pdo,
                    $user['id'],
                    'user.activate_failed',
                    ['user_id' => $user['id'], 'reason' => 'code_expired'],
                    $ipAddress
                );
            } else {
                // Activate the account and set role_id = 2
                $update = $pdo->prepare("
                    UPDATE `demon_users`
                    SET is_active = 1,
                        role_id = 2,
                        email_verified_at = NOW(),
                        status = 'active',
                        activation_token = NULL,
                        activation_expires = NULL,
                        updated_at = NOW()
                    WHERE id = :uid
                ");
                $update->execute([':uid' => $row['id']]);

                // At this point, the user is activated. Now award referral bonus if applicable.
                $activatedId  = (int)$row['id'];
                $referrerId   = isset($user['referrer_id']) ? (int)$user['referrer_id'] : null;

                if ($referrerId !== null) {
                    // Check if there's exactly one "registered" referral row
                    $checkReferral = $pdo->prepare("
                        SELECT COUNT(*) AS cnt
                        FROM `demon_referrals`
                        WHERE referrer_id = :rid
                          AND referred_user_id = :referred
                          AND status = 'registered'
                    ");
                    $checkReferral->execute([
                        'rid'      => $referrerId,
                        'referred' => $activatedId
                    ]);
                    $cntRow = $checkReferral->fetch(PDO::FETCH_ASSOC);

                    if ((int)$cntRow['cnt'] === 1) {
                        // Award the bonus now
                        $bonusAmount = 100; // adjust to your referral reward

                        try {
                            $pdo->beginTransaction();

                            // a) Insert into demon_credit_logs
                            $logStmt = $pdo->prepare("
                                INSERT INTO `demon_credit_logs`
                                  (user_id, change_amount, type,      reason,         description,       created_at)
                                VALUES
                                  (:rid,     :amt,         'earn',    'Referral Bonus', CONCAT('Activated user #', :referred), NOW())
                            ");
                            $logStmt->execute([
                                'rid'      => $referrerId,
                                'amt'      => $bonusAmount,
                                'referred' => $activatedId
                            ]);

                            // b) Update the referrer’s credit_balance
                            $balStmt = $pdo->prepare("
                                UPDATE `demon_users`
                                SET credit_balance = credit_balance + :amt
                                WHERE id = :rid
                            ");
                            $balStmt->execute([
                                'amt' => $bonusAmount,
                                'rid' => $referrerId
                            ]);

                            // c) Mark demon_referrals row as 'credited'
                            $refUpdate = $pdo->prepare("
                                UPDATE `demon_referrals`
                                SET status = 'credited',
                                    credited_at = NOW()
                                WHERE referrer_id = :rid
                                  AND referred_user_id = :referred
                                LIMIT 1
                            ");
                            $refUpdate->execute([
                                'rid'      => $referrerId,
                                'referred' => $activatedId
                            ]);

                            $pdo->commit();
                        } catch (\Exception $e) {
                            if ($pdo->inTransaction()) {
                                $pdo->rollBack();
                            }
                            error_log("Referral credit on activation failed: " . $e->getMessage());
                            // Optionally add: $errors[] = 'Could not award referral bonus at this time.';
                        }
                    }
                }

                $success = true;
                insert_audit_log(
                    $pdo,
                    $activatedId,
                    'user.activate_success',
                    ['user_id' => $activatedId],
                    $ipAddress
                );
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
          <div class="mb-4 mt-5">
            <i class="si si-lock-open fa-3x"></i>
          </div>
          <h1 class="h3 fw-bold mb-1">Authenticate your account</h1>
          <h2 class="fs-5 lh-base fw-normal text-muted mb-0">
            Please confirm your account by entering the 6-digit code sent to your email.
          </h2>
        </div>
        <!-- END Header -->

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if ($success === true): ?>
          <div class="alert alert-success text-center">
            <p>Your account is now activated!</p>
            <p>Redirecting you to login in <span id="countdown">5</span> seconds.</p>
            <p><a href="login.php">Click here</a> if you are not redirected.</p>
          </div>
          <script>
            (function() {
              let seconds = 5;
              const countdownEl = document.getElementById('countdown');
              const interval = setInterval(() => {
                seconds--;
                if (seconds <= 0) {
                  clearInterval(interval);
                  window.location.href = 'login.php';
                } else {
                  countdownEl.textContent = seconds;
                }
              }, 1000);
            })();
          </script>
          <div class="text-center mt-3">
            <a href="login.php" class="btn btn-alt-secondary">
              Back to Login Now
            </a>
          </div>
        <?php else: ?>
          <!-- Form -->
          <form id="form-activate"
                action="activate.php?email=<?= urlencode($email); ?>"
                method="POST"
                class="py-4">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email); ?>">
            <div class="block block-themed block-rounded block-fx-shadow text-center">
              <div class="block-header bg-gd-pulse">
                <h3 class="block-title">Enter Activation Code</h3>
              </div>
              <div class="block-content block-content-full">
                <div class="d-flex items-center justify-content-center gap-1 gap-sm-2 mb-4">
                  <input type="text" class="form-control form-control-lg text-center px-0"
                         id="num1" name="num1" maxlength="1" style="width: 38px;" required>
                  <input type="text" class="form-control form-control-lg text-center px-0"
                         id="num2" name="num2" maxlength="1" style="width: 38px;" required>
                  <input type="text" class="form-control form-control-lg text-center px-0"
                         id="num3" name="num3" maxlength="1" style="width: 38px;" required>
                  <span class="d-flex align-items-center">-</span>
                  <input type="text" class="form-control form-control-lg text-center px-0"
                         id="num4" name="num4" maxlength="1" style="width: 38px;" required>
                  <input type="text" class="form-control form-control-lg text-center px-0"
                         id="num5" name="num5" maxlength="1" style="width: 38px;" required>
                  <input type="text" class="form-control form-control-lg text-center px-0"
                         id="num6" name="num6" maxlength="1" style="width: 38px;" required>
                </div>
                <div>
                  <button type="submit" class="btn btn-lg btn-alt-primary px-6 py-2 fw-semibold">
                    Submit
                  </button>
                </div>
              </div>
              <div class="block-content block-content-full bg-body-light text-center fs-sm">
                Didn’t receive it?
                <a href="activate.php?email=<?= urlencode($email); ?>&resend=1">
                  Resend a new code
                </a>
              </div>
            </div>
          </form>
          <!-- END Form -->
          <div class="text-center mt-3">
            <a href="login.php" class="btn btn-alt-secondary">
              Back to Login
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<!-- END Page Content -->

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- Page JS Code -->
<?php $cb->get_js('js/pages/op_auth_two_factor.min.js'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // 1) Grab references to your six inputs by ID
  const inputs = [
    document.getElementById('num1'),
    document.getElementById('num2'),
    document.getElementById('num3'),
    document.getElementById('num4'),
    document.getElementById('num5'),
    document.getElementById('num6'),
  ].filter(el => el !== null);

  if (inputs.length !== 6) return; // bail if any input is missing

  // 2) Listen for a "paste" on ANY of these inputs
  inputs.forEach((inputBox) => {
    inputBox.addEventListener('paste', function(e) {
      e.preventDefault();

      // 3) Grab clipboard text
      const clipboard = (e.clipboardData || window.clipboardData).getData('text');

      // 4) Filter to digits only (0–9), take the first 6 characters
      const digits = clipboard.replace(/[^0-9]/g, '').slice(0, 6).split('');

      // 5) Fill inputs[0]..inputs[5] with those digits (including "0")
      digits.forEach((digit, idx) => {
        if (inputs[idx]) {
          inputs[idx].value = digit;
        }
      });

      // 6) If fewer than 6 digits, clear the rest
      for (let i = digits.length; i < inputs.length; i++) {
        inputs[i].value = '';
      }

      // 7) Move focus to the last filled box (or first empty if none pasted)
      const focusIndex = digits.length > 0
        ? Math.min(digits.length - 1, inputs.length - 1)
        : 0;
      inputs[focusIndex].focus();
    });
  });

  // 8) (Optional) Auto‐advance focus on single‐digit entry
  inputs.forEach((box, i) => {
    box.addEventListener('input', function() {
      if (box.value.length >= 1 && i < inputs.length - 1) {
        inputs[i + 1].focus();
      }
    });
  });
});
</script>

<?php require 'inc/_global/views/footer_end.php'; ?>
