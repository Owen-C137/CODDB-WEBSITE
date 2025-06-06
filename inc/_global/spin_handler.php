<?php
// inc/_global/spin_post.php
// --------------------------
//
// When invoked, this endpoint will:
//   1) Check if user has any `spin_allowance` > 0. If so, allow spin immediately (and decrement).
//      Otherwise, enforce the “once per 24 h” rule by checking the last ‘Wheel Spin’ in demon_credit_logs.
//   2) Load all segments from demon_spin_segments, pick one at random.
//   3) Award credits (if value>0) and insert appropriate log entries.
//   4) Return JSON: { success, prize_index, prize_value, remaining_spins }
//

require __DIR__ . '/config.php';         // loads $pdo, $site, etc.
require __DIR__ . '/login_check.php';    // ensures session_start() and $_SESSION['user_id']
header('Content-Type: application/json');

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not logged in.']);
    exit;
}

// 1) Check the user’s spin_allowance
$userStmt = $pdo->prepare("
    SELECT spin_allowance
    FROM demon_users
    WHERE id = :uid
    LIMIT 1
");
$userStmt->execute(['uid' => $userId]);
$userRow = $userStmt->fetch(PDO::FETCH_ASSOC);
$spinAllowance = (int)($userRow['spin_allowance'] ?? 0);

if ($spinAllowance <= 0) {
    // No extra allowance → enforce "once per 24 hours"
    $stmt = $pdo->prepare("
        SELECT MAX(created_at)
        FROM demon_credit_logs
        WHERE user_id = :uid
          AND reason  = 'Wheel Spin'
    ");
    $stmt->execute(['uid' => $userId]);
    $lastSpin = $stmt->fetchColumn(); // null or 'YYYY-MM-DD HH:MM:SS'

    if ($lastSpin) {
        $lastTime = strtotime($lastSpin);
        if (time() - $lastTime < 24 * 3600) {
            $remaining = 24 * 3600 - (time() - $lastTime);
            $h = floor($remaining / 3600);
            $m = floor(($remaining % 3600) / 60);
            echo json_encode([
                'success' => false,
                'error'   => "You can spin again in {$h}h {$m}m.",
                'remaining_spins' => 0
            ]);
            exit;
        }
    }
}

// 2) Fetch all segments from demon_spin_segments
$segStmt = $pdo->query("
    SELECT id, label, value
    FROM demon_spin_segments
    ORDER BY weight ASC, id ASC
");
$segments = $segStmt->fetchAll(PDO::FETCH_ASSOC);
if (!$segments) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'No spin segments found.']);
    exit;
}

// Build a zero‐indexed PHP array of just (label,value) for JS
$wheelData = [];
foreach ($segments as $row) {
    $wheelData[] = [
        'id'    => (int)$row['id'],
        'label' => $row['label'],
        'value' => (int)$row['value']
    ];
}

$maxIndex = count($wheelData) - 1;
$chosenIndex = random_int(0, $maxIndex);
$chosenValue = (int)$wheelData[$chosenIndex]['value'];

// 3) Begin transaction: insert a log + update user balance + possibly decrement spin_allowance
try {
    $pdo->beginTransaction();

    // a) Insert into demon_credit_logs as "Wheel Spin" (always, even if value=0)
    $desc = $chosenValue > 0
        ? "Wheel Spin: +{$chosenValue} credits"
        : "Wheel Spin: No reward";

    $logStmt = $pdo->prepare("
        INSERT INTO demon_credit_logs
          (user_id, change_amount, type, reason, description, created_at)
        VALUES
          (:uid, :amt, 'earn', 'Wheel Spin', :desc, NOW())
    ");
    $logStmt->execute([
        'uid'  => $userId,
        'amt'  => $chosenValue,
        'desc' => $desc
    ]);

    // b) If they “won” (>0), update demon_users.credit_balance
    if ($chosenValue > 0) {
        $updBal = $pdo->prepare("
            UPDATE demon_users
            SET credit_balance = credit_balance + :amt
            WHERE id = :uid
        ");
        $updBal->execute([
            'amt' => $chosenValue,
            'uid' => $userId
        ]);
    }

    // c) If spin_allowance > 0, decrement it by 1
    if ($spinAllowance > 0) {
        $deduct = $pdo->prepare("
            UPDATE demon_users
            SET spin_allowance = GREATEST(spin_allowance - 1, 0)
            WHERE id = :uid
        ");
        $deduct->execute(['uid' => $userId]);
        $spinAllowance--; // for response
    }

    $pdo->commit();
} catch (\Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
    exit;
}

// 4) Return JSON: prize_index, prize_value, remaining_spins
echo json_encode([
    'success'         => true,
    'prize_index'     => $chosenIndex,
    'prize_value'     => $chosenValue,
    'remaining_spins' => $spinAllowance
]);
exit;
