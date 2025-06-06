<?php
// inc/_global/claim_daily.php

require __DIR__ . '/config.php';
session_start();

// Always return JSON
header('Content-Type: application/json');

// 1) Must be logged in
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
if ($currentUserId === 0) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to claim your daily reward.'
    ]);
    exit;
}

// 2) CSRF token check
$csrfToken = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid CSRF token.'
    ]);
    exit;
}

// 3) Set reward amount
$dailyReward = 100;

// 4) Fetch user's current credit_balance, last_daily_claim, and spin_allowance
$stmt = $pdo->prepare("
    SELECT credit_balance, last_daily_claim, spin_allowance
    FROM demon_users
    WHERE id = :uid
    LIMIT 1
");
$stmt->execute(['uid' => $currentUserId]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userRow) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'User record not found.'
    ]);
    exit;
}

$lastClaim     = $userRow['last_daily_claim'];
$currentBalance = (int)$userRow['credit_balance'];
$currentSpins   = (int)$userRow['spin_allowance'];

$today = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d');

if ($lastClaim !== null && substr($lastClaim, 0, 10) === $today) {
    echo json_encode([
        'success' => false,
        'message' => 'You have already claimed your daily reward today. Come back tomorrow!'
    ]);
    exit;
}

// 5) Apply reward, increment spin_allowance, update DB, and insert logs + notification
try {
    $pdo->beginTransaction();

    $newBalance  = $currentBalance + $dailyReward;
    $newSpins    = $currentSpins + 1;

    // 5a) Update demon_users: credit_balance, last_daily_claim, spin_allowance
    $upd = $pdo->prepare("
        UPDATE demon_users
        SET
          credit_balance    = :new_balance,
          last_daily_claim  = :today,
          spin_allowance    = :new_spins
        WHERE id = :uid
    ");
    $upd->execute([
        'new_balance' => $newBalance,
        'today'       => $today,
        'new_spins'   => $newSpins,
        'uid'         => $currentUserId
    ]);

    // 5b) Insert a demon_credit_logs entry for the daily reward
    $log = $pdo->prepare("
        INSERT INTO demon_credit_logs
          (user_id, change_amount, type, reason, description, created_at, quest_key)
        VALUES
          (:uid, :amount, 'earn', 'Daily Reward', NULL, NOW(), 'daily_reward')
    ");
    $log->execute([
        'uid'    => $currentUserId,
        'amount' => $dailyReward
    ]);

    // 5c) Insert a demon_notifications entry so the user sees a “You claimed daily reward” message
    $notif = $pdo->prepare("
        INSERT INTO demon_notifications
          (user_id, message, link, is_read, created_at)
        VALUES
          (:uid, :msg, :link, 0, NOW())
    ");
    $notif->execute([
        'uid'   => $currentUserId,
        'msg'   => "You’ve claimed your daily reward of {$dailyReward} credits! Come back tomorrow for more.",
        'link'  => 'credits.php' // adjust if you want a different page
    ]);

    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your reward.'
    ]);
    exit;
}

// 6) Return success response
echo json_encode([
    'success'     => true,
    'message'     => "You have claimed {$dailyReward} credits! Your new balance is {$newBalance}. You also earned +1 free spin (now you have {$newSpins}).",
    'new_balance' => $newBalance,
    'new_spins'   => $newSpins
]);
exit;
