<?php
// credits.php

require 'inc/_global/config.php';
require 'inc/_global/login_check.php';   // ensures session, $pdo, $currentUserId
require 'inc/backend/config.php';

// (Optional) adjust layout size if using your $cb object
$cb->l_m_content = 'narrow';



// ────────────────────────────────────────────────────────────────────────────
// 0) HANDLE “TRANSFER CREDITS” FORM SUBMISSION (unchanged from before)
// ────────────────────────────────────────────────────────────────────────────
$transferErrors  = [];
$transferSuccess = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transfer_submit'])) {
    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $transferErrors[] = 'Invalid form submission. Please try again.';
    } else {
        $recipientInput = trim($_POST['recipient'] ?? '');
        $amountInput    = intval($_POST['amount'] ?? 0);

        // 0a) Validate amount
        $balStmt = $pdo->prepare("
            SELECT credit_balance, username
            FROM `demon_users`
            WHERE id = :uid
            LIMIT 1
        ");
        $balStmt->execute(['uid' => $currentUserId]);
        $senderRow      = $balStmt->fetch(PDO::FETCH_ASSOC);
        $currentBalance = (int)($senderRow['credit_balance'] ?? 0);
        $senderUsername = $senderRow['username'] ?? '';

        if ($amountInput < 1) {
            $transferErrors[] = 'Please enter an amount of at least 1.';
        } elseif ($amountInput > $currentBalance) {
            $transferErrors[] = 'Insufficient credits in your balance.';
        }

        // 0b) Lookup recipient by username
        if ($recipientInput === '') {
            $transferErrors[] = 'Recipient username is required.';
        } else {
            $recStmt = $pdo->prepare("
                SELECT id, username
                FROM `demon_users`
                WHERE username = :uname
                LIMIT 1
            ");
            $recStmt->execute(['uname' => $recipientInput]);
            $recRow = $recStmt->fetch(PDO::FETCH_ASSOC);

            if (!$recRow) {
                $transferErrors[] = 'Recipient not found. Please select a valid user.';
            } else {
                $recipientId   = (int)$recRow['id'];
                $recipientName = $recRow['username'];
                if ($recipientId === $currentUserId) {
                    $transferErrors[] = 'You cannot transfer credits to yourself.';
                }
            }
        }

        // 0c) If no validation errors, perform the transfer
        if (empty($transferErrors)) {
            try {
                $pdo->beginTransaction();

                // a) Deduct from sender’s balance and log
                $deductStmt = $pdo->prepare("
                    UPDATE `demon_users`
                    SET credit_balance = credit_balance - :amt
                    WHERE id = :uid
                ");
                $deductStmt->execute([
                    'amt' => $amountInput,
                    'uid' => $currentUserId
                ]);

                $logSend = $pdo->prepare("
                    INSERT INTO `demon_credit_logs`
                      (user_id, change_amount, type, reason, description, created_at)
                    VALUES
                      (:uid, -:amt, 'spend', 'Transfer to {$recipientName}', 'Sent {$amountInput} credits to {$recipientName}', NOW())
                ");
                $logSend->execute([
                    'uid' => $currentUserId,
                    'amt' => $amountInput
                ]);

                // b) Add to recipient’s balance and log
                $addStmt = $pdo->prepare("
                    UPDATE `demon_users`
                    SET credit_balance = credit_balance + :amt
                    WHERE id = :rid
                ");
                $addStmt->execute([
                    'amt' => $amountInput,
                    'rid' => $recipientId
                ]);

                $logReceive = $pdo->prepare("
                    INSERT INTO `demon_credit_logs`
                      (user_id, change_amount, type, reason, description, created_at)
                    VALUES
                      (:rid, :amt, 'earn', 'Transfer from {$senderUsername}', 'Received {$amountInput} credits from {$senderUsername}', NOW())
                ");
                $logReceive->execute([
                    'rid' => $recipientId,
                    'amt' => $amountInput
                ]);

                // c) Create a notification for recipient
                $notif = $pdo->prepare("
                    INSERT INTO `demon_notifications`
                      (user_id, message, link, created_at)
                    VALUES
                      (:rid, :msg, 'credits.php', NOW())
                ");
                $notif->execute([
                    'rid' => $recipientId,
                    'msg' => "{$senderUsername} has sent you {$amountInput} credits."
                ]);

                // d) Audit log the transfer
                $auditDetails = json_encode([
                    'from_user' => $currentUserId,
                    'to_user'   => $recipientId,
                    'amount'    => $amountInput
                ]);
                $ipAddr = $_SERVER['REMOTE_ADDR'] ?? '';
                $audit  = $pdo->prepare("
                    INSERT INTO `demon_audit_logs`
                      (user_id, action, details, ip_address, created_at)
                    VALUES
                      (:uid, 'credits.transfer', :details, :ip, NOW())
                ");
                $audit->execute([
                    'uid'     => $currentUserId,
                    'details' => $auditDetails,
                    'ip'      => $ipAddr
                ]);

                $pdo->commit();
                $transferSuccess = "Successfully sent {$amountInput} credits to {$recipientName}.";
                // Refresh current balance
                $currentBalance -= $amountInput;
            } catch (\Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $transferErrors[] = 'An error occurred while processing the transfer. Please try again.';
                error_log("Credit transfer error: " . $e->getMessage());
            }
        }
    }
}

// ────────────────────────────────────────────────────────────────────────────
// 1) FETCH CORE DATA (unchanged from before)
// ────────────────────────────────────────────────────────────────────────────

// a) Current credit balance (for non-AJAX fallback)
if (!isset($currentBalance)) {
    $balStmt = $pdo->prepare("
        SELECT credit_balance
        FROM `demon_users`
        WHERE id = :uid
        LIMIT 1
    ");
    $balStmt->execute(['uid' => $currentUserId]);
    $currentBalance = (int)$balStmt->fetchColumn();
}

// b) Last 50 credit-log entries
$logStmt = $pdo->prepare("
    SELECT change_amount, reason, description, created_at
    FROM `demon_credit_logs`
    WHERE user_id = :uid
    ORDER BY created_at DESC
    LIMIT 50
");
$logStmt->execute(['uid' => $currentUserId]);
$creditLogs = $logStmt->fetchAll(PDO::FETCH_ASSOC);

// c) TOTAL EARNED and TOTAL SPENT (all-time)
$sumStmt = $pdo->prepare("
    SELECT
      SUM(CASE WHEN change_amount > 0 THEN change_amount ELSE 0 END) AS total_earned,
      SUM(CASE WHEN change_amount < 0 THEN ABS(change_amount) ELSE 0 END) AS total_spent
    FROM `demon_credit_logs`
    WHERE user_id = :uid
");
$sumStmt->execute(['uid' => $currentUserId]);
$sumRow     = $sumStmt->fetch(PDO::FETCH_ASSOC);
$totalEarned = (int)$sumRow['total_earned'];
$totalSpent  = (int)$sumRow['total_spent'];

// d) CREDITS EARNED/SPENT THIS MONTH
$monthStmt = $pdo->prepare("
    SELECT
      SUM(CASE WHEN change_amount > 0 THEN change_amount ELSE 0 END) AS month_earned,
      SUM(CASE WHEN change_amount < 0 THEN ABS(change_amount) ELSE 0 END) AS month_spent
    FROM `demon_credit_logs`
    WHERE user_id = :uid
      AND YEAR(created_at) = YEAR(CURDATE())
      AND MONTH(created_at) = MONTH(CURDATE())
");
$monthStmt->execute(['uid' => $currentUserId]);
$monthRow   = $monthStmt->fetch(PDO::FETCH_ASSOC);
$monthEarned = (int)$monthRow['month_earned'];
$monthSpent  = (int)$monthRow['month_spent'];

// e) CREDITS EARNED/SPENT THIS WEEK (ISO week)
$weekStmt = $pdo->prepare("
    SELECT
      SUM(CASE WHEN change_amount > 0 THEN change_amount ELSE 0 END) AS week_earned,
      SUM(CASE WHEN change_amount < 0 THEN ABS(change_amount) ELSE 0 END) AS week_spent
    FROM `demon_credit_logs`
    WHERE user_id = :uid
      AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
");
$weekStmt->execute(['uid' => $currentUserId]);
$weekRow   = $weekStmt->fetch(PDO::FETCH_ASSOC);
$weekEarned = (int)$weekRow['week_earned'];
$weekSpent  = (int)$weekRow['week_spent'];

// f) FETCH DATA FOR LAST 30 DAYS (FOR CHART.JS)
//    Net daily change = SUM(change_amount) GROUP BY date
$chartStmt   = $pdo->prepare("
    SELECT
      DATE(created_at) AS day,
      SUM(change_amount) AS net_change
    FROM `demon_credit_logs`
    WHERE user_id = :uid
      AND created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) ASC
");
$chartStmt->execute(['uid' => $currentUserId]);
$chartDataRaw = $chartStmt->fetchAll(PDO::FETCH_ASSOC);

// Build two parallel arrays: labels (YYYY-MM-DD) and data (net_change)
$chartLabels = [];
$chartValues = [];
for ($i = 29; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $chartLabels[]    = $d;
    $chartValues[$d] = 0;
}
foreach ($chartDataRaw as $row) {
    $day             = $row['day'];
    $chartValues[$day] = (int)$row['net_change'];
}
$chartData = array_values($chartValues);
unset($chartValues);

// g) REFERRAL DATA (unchanged from before)
$referralCode = null;
$columnCheck = $pdo->query("
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'demon_users'
      AND COLUMN_NAME = 'referral_code'
")->fetchColumn();

if ((int)$columnCheck === 1) {
    $userStmt = $pdo->prepare("
        SELECT referral_code
        FROM `demon_users`
        WHERE id = :uid
        LIMIT 1
    ");
    $userStmt->execute(['uid' => $currentUserId]);
    $refRow       = $userStmt->fetch(PDO::FETCH_ASSOC);
    $referralCode = $refRow['referral_code'] ?? null;
}

if ($referralCode) {
    $scheme       = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https';
    $referralLink = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/register.php?ref=' . urlencode($referralCode);
} else {
    $referralLink = null;
}

$statusStmt = $pdo->prepare("
    SELECT
      SUM(status = 'pending') AS pending_count,
      SUM(status = 'registered') AS registered_count,
      SUM(status = 'credited') AS credited_count
    FROM `demon_referrals`
    WHERE referrer_id = :uid
");
$statusStmt->execute(['uid' => $currentUserId]);
$statusCounts       = $statusStmt->fetch(PDO::FETCH_ASSOC);
$pendingInvites      = (int)$statusCounts['pending_count'];
$registeredInvites   = (int)$statusCounts['registered_count'];
$creditedInvites     = (int)$statusCounts['credited_count'];

$refSumStmt = $pdo->prepare("
    SELECT SUM(change_amount) AS total_referral_credits
    FROM `demon_credit_logs`
    WHERE user_id = :uid
      AND reason = 'Referral Bonus'
");
$refSumStmt->execute(['uid' => $currentUserId]);
$totalReferralCredits = (int)$refSumStmt->fetchColumn();

// h) CREDIT LEVEL / TIER (unchanged from before)
$currentTier      = null;
$nextTierName     = null;
$nextTierRequired = null;

$tableCheck = $pdo->query("
    SELECT COUNT(*)
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'demon_credit_tiers'
")->fetchColumn();

if ((int)$tableCheck === 1) {
    $tierStmt = $pdo->query("
        SELECT tier_name, min_credits_required
        FROM `demon_credit_tiers`
        ORDER BY min_credits_required ASC
    ");
    $tiers = $tierStmt->fetchAll(PDO::FETCH_ASSOC);

    $currentTier = 'None';
    foreach ($tiers as $t) {
        if ($currentBalance >= $t['min_credits_required']) {
            $currentTier = $t['tier_name'];
        } else {
            if ($nextTierName === null) {
                $nextTierName     = $t['tier_name'];
                $nextTierRequired = (int)$t['min_credits_required'];
            }
        }
    }
}

// i) FETCH ALL OTHER REGISTERED USERS FOR TRANSFER LIST (unchanged)
$userListStmt = $pdo->prepare("
    SELECT username
    FROM `demon_users`
    WHERE id != :uid
    ORDER BY username ASC
");
$userListStmt->execute(['uid' => $currentUserId]);
$userList = $userListStmt->fetchAll(PDO::FETCH_COLUMN);

?>

<?php require 'inc/_global/views/head_start.php'; ?>
<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<div class="content">
  <h2 class="content-heading">Your Credits Dashboard</h2>

  <!-- =============================== -->
  <!-- 1) Top Row: Claim + Balance + Summary Cards -->
  <!-- =============================== -->
  <div class="row gx-4 mb-4">
    <!-- 1a) Claim Daily + Current Balance -->
    <div class="col-md-12">
      <div class="card shadow-sm">
        <div class="row g-0 align-items-center">
          <!-- Left: Claim Daily -->
          <div class="col-lg-4 border-end">
            <div class="p-4 d-flex flex-column justify-content-center h-100">
              <button id="claim-daily-btn"
                      class="btn btn-lg btn-alt-success w-100 mb-3"
                      data-csrf="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                <i class="fa fa-calendar-day me-1"></i>
                Claim Daily Rewards
              </button>
              <a href="/spin.php"
                id="claim-daily-btn"
                class="btn btn-lg btn-alt-success w-100 mb-3">
                <i class="fa fa-spinner"></i>
                Spin The Wheel
              </a>
              <small class="text-muted">
                You can claim once every 24 hours.
              </small>
            </div>
          </div>
          <!-- Right: Current Balance -->
          <div class="col-lg-8">
            <div class="p-4 text-center text-lg-start">
              <div class="d-flex align-items-center justify-content-center justify-content-lg-start">
                <i class="fa fa-coins fa-2x text-warning me-3"></i>
                <div>
                  <div class="fs-6 text-muted">Current Balance</div>
                  <div class="fs-2 fw-bold text-primary" id="credit-balance">
                    <?= number_format($currentBalance); ?>
                  </div>
                  <div class="fs-sm text-muted">Credits</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 1b) Summary Cards -->
    <div class="col-md-3 mt-4">
      <div class="card text-center shadow-sm">
        <div class="card-body p-3">
          <div class="fs-sm text-muted">Total Earned</div>
          <div class="fs-3 fw-bold text-success"><?= number_format($totalEarned); ?></div>
          <div class="fs-xs text-muted">Credits</div>
        </div>
      </div>
    </div>
    <div class="col-md-3 mt-4">
      <div class="card text-center shadow-sm">
        <div class="card-body p-3">
          <div class="fs-sm text-muted">Total Spent</div>
          <div class="fs-3 fw-bold text-danger"><?= number_format($totalSpent); ?></div>
          <div class="fs-xs text-muted">Credits</div>
        </div>
      </div>
    </div>
    <div class="col-md-3 mt-4">
      <div class="card text-center shadow-sm">
        <div class="card-body p-3">
          <div class="fs-sm text-muted">This Month (Earn − Spent)</div>
          <?php $netM = $monthEarned - $monthSpent; ?>
          <div class="fs-3 fw-bold <?= $netM >= 0 ? 'text-success' : 'text-danger'; ?>">
            <?= number_format($netM); ?>
          </div>
          <div class="fs-xs text-muted">Credits</div>
        </div>
      </div>
    </div>
    <div class="col-md-3 mt-4">
      <div class="card text-center shadow-sm">
        <div class="card-body p-3">
          <div class="fs-sm text-muted">This Week (Earn − Spent)</div>
          <?php $netW = $weekEarned - $weekSpent; ?>
          <div class="fs-3 fw-bold <?= $netW >= 0 ? 'text-success' : 'text-danger'; ?>">
            <?= number_format($netW); ?>
          </div>
          <div class="fs-xs text-muted">Credits</div>
        </div>
      </div>
    </div>
  </div>

  <!-- AJAX alert placeholder -->
  <div id="claim-alert-container" class="mb-4"></div>

  <!-- =============================== -->
  <!-- 2) Referral / Invite Section -->
  <!-- =============================== -->
  <div class="row gx-4 mb-4">
    <div class="col-md-12">
      <div class="card shadow-sm">
        <div class="card-header bg-body-light">
          <h3 class="card-title mb-0">Invite Friends & Earn</h3>
        </div>
        <div class="card-body">
          <?php if ($referralLink): ?>
            <!-- 2.1) Referral Link + Copy Button -->
            <div class="mb-3">
              <label class="form-label">Your Referral Link</label>
              <div class="input-group">
                <input type="text" readonly
                       class="form-control"
                       id="ref-link"
                       value="<?= htmlspecialchars($referralLink); ?>">
                <button class="btn btn-outline-secondary" id="copy-ref-btn" type="button">
                  <i class="fa fa-copy"></i> Copy
                </button>
              </div>
              <small class="text-muted">Share this link. You’ll earn credits when someone signs up with it.</small>
            </div>

            <!-- 2.2) Quick Counts of Each Invite Status -->
            <div class="row text-center">
              <div class="col-lg-3 col-sm-6 mb-3">
                <div class="fs-xs text-muted">Pending Invites</div>
                <div class="fs-4 fw-bold <?= $pendingInvites ? 'text-warning' : 'text-muted'; ?>">
                  <?= number_format($pendingInvites); ?>
                </div>
                <div class="fs-xs text-muted">(Awaiting Signup)</div>
              </div>
              <div class="col-lg-3 col-sm-6 mb-3">
                <div class="fs-xs text-muted">Awaiting Activation</div>
                <div class="fs-4 fw-bold <?= $registeredInvites ? 'text-primary' : 'text-muted'; ?>">
                  <?= number_format($registeredInvites); ?>
                </div>
                <div class="fs-xs text-muted">(Not Credited)</div>
              </div>
              <div class="col-lg-3 col-sm-6 mb-3">
                <div class="fs-xs text-muted">Credited Invites</div>
                <div class="fs-4 fw-bold <?= $creditedInvites ? 'text-success' : 'text-muted'; ?>">
                  <?= number_format($creditedInvites); ?>
                </div>
                <div class="fs-xs text-muted">(Bonus Given)</div>
              </div>
              <div class="col-lg-3 col-sm-6 mb-3">
                <div class="fs-xs text-muted">Referral Credits Earned</div>
                <div class="fs-4 fw-bold text-success">
                  <?= number_format($totalReferralCredits); ?>
                </div>
                <div class="fs-xs text-muted">Credits</div>
              </div>
            </div>

            <!-- 2.3) View My Invites Button (optional) -->
            <div class="text-end mt-2">
              <a href="/my_invites.php" class="btn btn-sm btn-alt-info">
                <i class="fa fa-list me-1"></i> View My Invites
              </a>
            </div>
          <?php else: ?>
            <p class="text-danger">
              Referral program not available. Contact an administrator if you believe this is an error.
            </p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- =============================== -->
  <!-- 3) Credit Activity Chart (Last 30 Days) -->
  <!-- =============================== -->
  <div class="row gx-4 mb-4">
    <div class="col-md-12">
      <div class="card shadow-sm">
        <div class="card-header bg-body-light">
          <h3 class="card-title mb-0">Activity (Last 30 Days)</h3>
        </div>
        <div class="card-body">
          <canvas id="creditChart" height="100"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- =============================== -->
  <!-- 4) Credit Level / Tier Progress -->
  <!-- =============================== -->
  <?php if ($currentTier !== null): ?>
  <div class="row gx-4 mb-4">
    <div class="col-md-12">
      <div class="card shadow-sm">
        <div class="card-header bg-body-light">
          <h3 class="card-title mb-0">Your Tier: <?= htmlspecialchars($currentTier); ?></h3>
        </div>
        <div class="card-body">
          <?php if ($nextTierName): 
            $needed          = $nextTierRequired - $currentBalance;
            $progressPercent = round(($currentBalance / $nextTierRequired) * 100, 2);
            if ($progressPercent > 100) $progressPercent = 100;
          ?>
            <div class="mb-2">
              <div class="d-flex justify-content-between">
                <span><?= htmlspecialchars($currentTier); ?></span>
                <span><?= htmlspecialchars($nextTierName); ?></span>
              </div>
              <div class="progress" style="height: 1rem;">
                <div class="progress-bar" role="progressbar"
                     style="width: <?= $progressPercent; ?>%;"
                     aria-valuenow="<?= $progressPercent; ?>"
                     aria-valuemin="0" aria-valuemax="100">
                  <?= $progressPercent; ?>%
                </div>
              </div>
            </div>
            <p class="mb-0">
              You need <strong><?= number_format($needed); ?></strong> more credits to reach <strong><?= htmlspecialchars($nextTierName); ?></strong>.
            </p>
          <?php else: ?>
            <p class="mb-0">You are at the highest tier! Thank you for being a top user.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- =============================== -->
  <!-- 5) Quick Transfer Form -->
  <!-- =============================== -->
  <div class="row gx-4 mb-4">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-body-light">
          <h3 class="card-title mb-0">Transfer Credits to a Friend</h3>
        </div>
        <div class="card-body">
          <?php if ($transferSuccess): ?>
            <div class="alert alert-success">
              <?= htmlspecialchars($transferSuccess); ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($transferErrors)): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($transferErrors as $err): ?>
                  <li><?= htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form id="transfer-form" action="credits.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

            <!-- ====== Scrollable <select> for Recipients ====== -->
            <div class="mb-3">
              <label for="transfer-username" class="form-label">Recipient Username</label>
              <div style="max-height: 200px; overflow-y: auto;">
                <select class="form-select" id="transfer-username" name="recipient" required>
                  <option value="" disabled selected>— Select a user —</option>
                  <?php foreach ($userList as $uname): ?>
                    <option value="<?= htmlspecialchars($uname); ?>">
                      <?= htmlspecialchars($uname); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <!-- ====================================================== -->

            <div class="mb-3">
              <label for="transfer-amount" class="form-label">Amount</label>
              <input type="number" min="1" max="<?= $currentBalance; ?>" class="form-control" id="transfer-amount" name="amount" required>
              <div class="form-text">You have <?= number_format($currentBalance); ?> credits available.</div>
            </div>
            <button type="submit" name="transfer_submit" class="btn btn-alt-primary">
              <i class="fa fa-paper-plane me-1"></i> Send Credits
            </button>
          </form>
          <small class="text-muted">Transfers are instant but cannot be undone. Make sure you enter the correct recipient.</small>
        </div>
      </div>
    </div>
  </div>

  <!-- =============================== -->
  <!-- 6) Credit History (Last 50 Entries) with “Show More” Button -->
  <!-- =============================== -->
  <div class="row gx-4">
    <div class="col-md-12">
      <div class="card shadow-sm">
        <div class="card-header bg-body-light d-flex justify-content-between align-items-center">
          <h3 class="card-title mb-0">Credit History (Last 50 Entries)</h3>
          <button id="load-more-logs" class="btn btn-sm btn-alt-secondary">
            <i class="fa fa-sync-alt me-1"></i> Load More
          </button>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead class="table">
                <tr>
                  <th class="fs-xs text-muted">Date</th>
                  <th class="fs-xs text-muted">Description</th>
                  <th class="fs-xs text-muted text-end">Change</th>
                </tr>
              </thead>
              <tbody id="history-body">
                <?php if (empty($creditLogs)): ?>
                  <tr>
                    <td colspan="3" class="text-center text-muted py-4">
                      No credit activity yet.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($creditLogs as $log):
                    $desc    = trim($log['description'] ?? '') !== '' ? $log['description'] : $log['reason'];
                    $amount  = (int)$log['change_amount'];
                    $isEarn  = ($amount > 0);
                  ?>
                    <tr>
                      <td class="fs-sm">
                        <?= date('j M Y, H:i', strtotime($log['created_at'])); ?>
                      </td>
                      <td class="fs-sm">
                        <?= htmlspecialchars($desc); ?>
                      </td>
                      <td class="fs-sm text-end">
                        <?php if ($isEarn): ?>
                          <span class="badge bg-success">
                            +<?= number_format($amount); ?>
                          </span>
                        <?php else: ?>
                          <span class="badge bg-danger">
                            −<?= number_format(abs($amount)); ?>
                          </span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- =============================== -->
  <!-- 7) Tips / Upcoming Ways to Earn -->
  <!-- =============================== -->
  <div class="row gx-4 mt-4">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-body-light">
          <h3 class="card-title mb-0">Upcoming Ways to Earn Credits</h3>
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <li class="list-group-item">
              <strong>Complete Your Profile:</strong> +50 credits
            </li>
            <li class="list-group-item">
              <strong>Refer a Friend:</strong> +100 credits when they sign up
            </li>
            <li class="list-group-item">
              <strong>First Purchase Bonus:</strong> +20 credits on first item
            </li>
            <li class="list-group-item">
              <strong>Daily Login Streak:</strong> +10 extra credits for 7-day streak
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

</div>
<!-- END Page Content -->

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- =============================== -->
<!-- JavaScript Section (Chart.js, AJAX “Load More”, etc.) -->
<!-- =============================== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // ------------------------------------------------------------
  // a) Claim Daily Button
  // ------------------------------------------------------------
  const claimBtn   = document.getElementById('claim-daily-btn');
  const balanceEl  = document.getElementById('credit-balance');
  const alertCont  = document.getElementById('claim-alert-container');

  if (claimBtn) {
    claimBtn.addEventListener('click', function(e) {
      e.preventDefault();
      alertCont.innerHTML = '';

      const csrfToken = claimBtn.getAttribute('data-csrf');
      fetch('/inc/_global/claim_daily.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ csrf_token: csrfToken })
      })
      .then(res => res.json())
      .then(data => {
        const alertDiv = document.createElement('div');
        alertDiv.classList.add(
          'alert',
          data.success ? 'alert-success' : 'alert-danger',
          'alert-dismissible',
          'fade',
          'show'
        );
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
          ${data.message}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        alertCont.appendChild(alertDiv);

        if (data.success && typeof data.new_balance !== 'undefined') {
          balanceEl.textContent = data.new_balance;
          claimBtn.disabled  = true;
        }

        setTimeout(() => {
          if (alertDiv.classList.contains('show')) {
            bootstrap.Alert.getOrCreateInstance(alertDiv).close();
          }
        }, 5000);
      })
      .catch(err => {
        console.error(err);
        const alertDiv = document.createElement('div');
        alertDiv.classList.add('alert', 'alert-danger', 'alert-dismissible', 'fade', 'show');
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
          An error occurred. Please try again.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        alertCont.appendChild(alertDiv);
        setTimeout(() => {
          if (alertDiv.classList.contains('show')) {
            bootstrap.Alert.getOrCreateInstance(alertDiv).close();
          }
        }, 5000);
      });
    });
  }

  // ------------------------------------------------------------
  // b) Copy Referral Link Button
  // ------------------------------------------------------------
  const copyRefBtn = document.getElementById('copy-ref-btn');
  if (copyRefBtn) {
    copyRefBtn.addEventListener('click', function() {
      const refInput = document.getElementById('ref-link');
      refInput.select();
      refInput.setSelectionRange(0, 99999);
      document.execCommand('copy');
      copyRefBtn.innerHTML = '<i class="fa fa-check"></i> Copied';
      setTimeout(() => {
        copyRefBtn.innerHTML = '<i class="fa fa-copy"></i> Copy';
      }, 2000);
    });
  }

  // ------------------------------------------------------------
  // c) Credit Activity Chart (Chart.js)
  // ------------------------------------------------------------
  const ctx   = document.getElementById('creditChart').getContext('2d');
  const chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?= json_encode($chartLabels); ?>,
      datasets: [{
        label: 'Net Change',
        data: <?= json_encode($chartData); ?>,
        borderColor: 'rgba(40, 167, 69, 0.8)',
        backgroundColor: 'rgba(40, 167, 69, 0.2)',
        tension: 0.3,
        fill: true,
        pointRadius: 3,
        pointBackgroundColor: 'rgba(40, 167, 69, 1)'
      }]
    },
    options: {
      scales: {
        x: {
          ticks: {
            maxRotation: 0,
            autoSkip: true,
            maxTicksLimit: 10
          }
        },
        y: {
          beginAtZero: true
        }
      },
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              let v = context.parsed.y;
              return (v >= 0 ? '+' : '') + v + ' credits';
            }
          }
        }
      }
    }
  });

  // ------------------------------------------------------------
  // d) Load More History (AJAX)
  // ------------------------------------------------------------
  let historyOffset = 50; // already loaded 50
  const loadMoreBtn = document.getElementById('load-more-logs');
  const historyBody = document.getElementById('history-body');

  if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', function() {
      loadMoreBtn.disabled   = true;
      loadMoreBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Loading...';

      fetch(`/inc/_global/load_more_credits.php?offset=${historyOffset}`)
        .then(res => res.json())
        .then(data => {
          if (data.success && data.logs.length > 0) {
            data.logs.forEach(log => {
              const tr       = document.createElement('tr');
              const dateTd   = document.createElement('td');
              dateTd.classList.add('fs-sm');
              dateTd.textContent = log.formatted_date;

              const descTd   = document.createElement('td');
              descTd.classList.add('fs-sm');
              descTd.textContent = log.description;

              const changeTd = document.createElement('td');
              changeTd.classList.add('fs-sm', 'text-end');
              const badge    = document.createElement('span');
              if (log.change_amount > 0) {
                badge.classList.add('badge', 'bg-success');
                badge.textContent = '+' + Number(log.change_amount).toLocaleString();
              } else {
                badge.classList.add('badge', 'bg-danger');
                badge.textContent = '−' + Number(Math.abs(log.change_amount)).toLocaleString();
              }
              changeTd.appendChild(badge);
              tr.appendChild(dateTd);
              tr.appendChild(descTd);
              tr.appendChild(changeTd);
              historyBody.appendChild(tr);
            });
            historyOffset += data.logs.length;
            loadMoreBtn.disabled = false;
            loadMoreBtn.innerHTML = '<i class="fa fa-sync-alt me-1"></i> Load More';
            if (!data.hasMore) {
              loadMoreBtn.textContent = 'No More Entries';
              loadMoreBtn.disabled   = true;
            }
          } else {
            loadMoreBtn.textContent = 'No More Entries';
            loadMoreBtn.disabled   = true;
          }
        })
        .catch(err => {
          console.error(err);
          loadMoreBtn.textContent = 'Error Loading';
        });
    });
  }

  // ------------------------------------------------------------
  // e) Transfer Form Validation
  // ------------------------------------------------------------
  const transferForm = document.getElementById('transfer-form');
  if (transferForm) {
    transferForm.addEventListener('submit', function(e) {
      const amountInput = document.getElementById('transfer-amount');
      const amt         = parseInt(amountInput.value, 10);
      if (isNaN(amt) || amt < 1 || amt > <?= $currentBalance; ?>) {
        e.preventDefault();
        alert('Please enter a valid amount (1 to <?= $currentBalance; ?>).');
      }
    });
  }
});
</script>

<?php require 'inc/_global/views/footer_end.php'; ?>
