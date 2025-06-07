<?php
// donate.php
/**
 * Hosted PayPal Donate Button (ID: TP6GNFXCBPHBE)
 * Button URL:         https://www.paypal.com/donate/?hosted_button_id=TP6GNFXCBPHBE
 * Success URL:        https://bo3.coddb.net/donate.php?status=success
 * Cancel URL:         https://bo3.coddb.net/donate.php?status=cancel
 * IPN Listener URL:   https://bo3.coddb.net/ipn.php
 */

require 'inc/_global/config.php';      // sets up $pdo, etc.
require 'inc/_global/login_check.php'; // ensures logged-in
require 'inc/backend/config.php';      // any backend PHP setup

// ────────────────────────────────────────────────────────────────
// 1) Handle “return” statuses (purely UI; no DB writes here)
// ────────────────────────────────────────────────────────────────
$statusType    = null;
$statusTitle   = '';
$statusContent = '';

if (!empty($_GET['status'])) {
    switch ($_GET['status']) {
        case 'success':
            $statusType    = 'success';
            $statusTitle   = '🎉 Donation Complete';
            $statusContent = 'Thank you for your generous support!';
            break;
        case 'cancel':
            $statusType    = 'warning';
            $statusTitle   = '⚠️ Donation Cancelled';
            $statusContent = 'You cancelled the donation. Feel free to try again!';
            break;
    }
}

// ────────────────────────────────────────────────────────────────
// 2) Render Page
// ────────────────────────────────────────────────────────────────
$cb->l_m_content = 'narrow';
require 'inc/_global/views/head_start.php';
require 'inc/_global/views/head_end.php';
require 'inc/_global/views/page_start.php';
?>

<div class="content">

  <!-- 2.1) Success / Cancel Card -->
  <?php if ($statusType): ?>
    <div class="block block-rounded mb-4 border-<?= $statusType ?>">
      <div class="block-content text-center py-4">
        <i class="fa fa-<?= $statusType==='success' ? 'check-circle text-success' : 'exclamation-triangle text-warning' ?> fa-3x mb-2"></i>
        <h4 class="fw-semibold text-<?= $statusType ?> mb-1"><?= htmlspecialchars($statusTitle) ?></h4>
        <p class="mb-0"><?= htmlspecialchars($statusContent) ?></p>
      </div>
    </div>
  <?php endif; ?>

  <!-- 2.2) Donation Block -->
  <div class="row justify-content-center">
    <div class="col-md-6 col-xl-4">
      <div class="block block-rounded block-bordered">
        <div class="block-header block-header-default">
          <h3 class="block-title">
            <i class="fa fa-paypal me-1"></i> Donate with PayPal
          </h3>
        </div>
        <div class="block-content text-center">

          <!-- 2.3) Hosted PayPal Form -->
          <form action="https://www.paypal.com/donate" method="post" target="_blank">
            <input type="hidden" name="hosted_button_id" value="TP6GNFXCBPHBE">
            <input type="hidden" name="custom"            value="<?= (int)$_SESSION['user_id'] ?>">
            <input type="hidden" name="return"            value="https://<?= $_SERVER['HTTP_HOST'] ?>/donate.php?status=success">
            <input type="hidden" name="cancel_return"     value="https://<?= $_SERVER['HTTP_HOST'] ?>/donate.php?status=cancel">
            <input type="hidden" name="notify_url"        value="https://<?= $_SERVER['HTTP_HOST'] ?>/ipn.php">
            <button type="submit" class="btn btn-alt-info btn-lg">
              <i class="fab fa-paypal me-1"></i> Donate Now
            </button>
          </form>

        </div>
      </div>
    </div>
  </div>

</div>

<?php
require 'inc/_global/views/page_end.php';
require 'inc/_global/views/footer_start.php';
require 'inc/_global/views/footer_end.php';
