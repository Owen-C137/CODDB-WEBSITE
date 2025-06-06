<?php
// inc/_global/quests/spin_wheel.php
//
// Each time "all quests" are checked (i.e. whenever index.php is required), this script will:
//  1) Look up the user’s most recent spin (from demon_spin_history).
//  2) If that spin happened strictly after the user’s last recorded completion for “spin_wheel,” it:
//     a) Updates/inserts demon_user_quests (quest_id = 8, because that’s spin_wheel).
//     b) If demon_quests.reward_amount > 0, awards that reward (in demon_credit_logs + demon_users.credit_balance).
//
// “spin_wheel” is repeatable (is_repeatable = 1), so every new spin = a new quest completion event.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId === 0) {
    // Not logged in → nothing to do.
    return;
}

//
// 1) Fetch the “spin_wheel” quest definition:
//
$questKey = 'spin_wheel';
$qStmt = $pdo->prepare("
    SELECT id, reward_amount, is_repeatable, is_active
    FROM demon_quests
    WHERE quest_key = :qk
      AND is_active = 1
    LIMIT 1
");
$qStmt->execute(['qk' => $questKey]);
$quest = $qStmt->fetch(PDO::FETCH_ASSOC);

if (!$quest) {
    // No active "spin_wheel" quest exists, so bail out.
    return;
}

$questId       = (int)$quest['id'];
$rewardAmount  = (int)$quest['reward_amount'];
$isRepeatable  = (bool)$quest['is_repeatable'];

//
// 2) Fetch the user’s very latest spin from demon_spin_history:
//
$spinStmt = $pdo->prepare("
    SELECT created_at
    FROM demon_spin_history
    WHERE user_id = :uid
    ORDER BY created_at DESC
    LIMIT 1
");
$spinStmt->execute(['uid' => $userId]);
$lastSpinRow = $spinStmt->fetch(PDO::FETCH_ASSOC);

if (!$lastSpinRow) {
    // The user has never spun → nothing to record.
    return;
}

$lastSpinAt = strtotime($lastSpinRow['created_at']); // timestamp of the latest spin

//
// 3) Fetch the user’s existing demon_user_quests record for “spin_wheel” (if any):
//
$userQuestStmt = $pdo->prepare("
    SELECT id, progress_count, completed_at
    FROM demon_user_quests
    WHERE user_id   = :uid
      AND quest_id  = :qid
    LIMIT 1
");
$userQuestStmt->execute([
    'uid' => $userId,
    'qid' => $questId
]);
$userQuestRow = $userQuestStmt->fetch(PDO::FETCH_ASSOC);

//
// 4) Decide whether this “latest spin” is newer than the last time we marked “spin_wheel” completed.
//
$shouldAward = false;

if (!$userQuestRow) {
    // No row yet → we’ve never marked this quest complete for the user,
    // but they’ve spun at least once (because $lastSpinRow exists). Award now.
    $shouldAward = true;
} else {
    // They already have a row; only award again if:
    //   (a) is_repeatable = true
    //   (b) lastSpinAt > UNIX_TIMESTAMP( completed_at )
    //
    if ($isRepeatable) {
        $completedAt = strtotime($userQuestRow['completed_at']);
        if ($lastSpinAt > $completedAt) {
            $shouldAward = true;
        }
    }
}

if (! $shouldAward) {
    return;
}

// At this point, we know “there’s a new spin to count.”

try {
    $pdo->beginTransaction();

    if (! $userQuestRow) {
        // Insert a new row for demon_user_quests:
        $insertQuest = $pdo->prepare("
            INSERT INTO demon_user_quests
              (user_id, quest_key, quest_id, progress_count, is_completed, completed_at, last_updated)
            VALUES
              (:uid, :qk, :qid, 1, 1, NOW(), NOW())
        ");
        $insertQuest->execute([
            'uid' => $userId,
            'qk'  => $questKey,
            'qid' => $questId
        ]);

        $userQuestId = $pdo->lastInsertId();
    } else {
        // Update existing row: bump progress_count, set completed_at = NOW()
        $updateQuest = $pdo->prepare("
            UPDATE demon_user_quests
            SET progress_count = progress_count + 1,
                is_completed   = 1,
                completed_at   = NOW(),
                last_updated   = NOW()
            WHERE id = :id
        ");
        $updateQuest->execute(['id' => $userQuestRow['id']]);
        $userQuestId = $userQuestRow['id'];
    }

    // 5) If the quest’s reward_amount > 0, give that reward now:
    if ($rewardAmount > 0) {
        // a) Insert into demon_credit_logs with reason = "Quest:spin_wheel"
        $logDesc = "Quest: spin_wheel";
        $logStmt = $pdo->prepare("
            INSERT INTO demon_credit_logs
              (user_id, change_amount, type, reason, description, created_at, quest_key)
            VALUES
              (:uid, :amt, 'earn', :r, :desc, NOW(), :qk)
        ");
        $logStmt->execute([
            'uid'  => $userId,
            'amt'  => $rewardAmount,
            'r'    => 'Quest: spin_wheel',
            'desc' => $logDesc,
            'qk'   => $questKey
        ]);

        // b) Update demon_users.credit_balance
        $updBal = $pdo->prepare("
            UPDATE demon_users
            SET credit_balance = credit_balance + :amt
            WHERE id = :uid
        ");
        $updBal->execute([
            'amt' => $rewardAmount,
            'uid' => $userId
        ]);
    }

    $pdo->commit();
} catch (\Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Don’t throw further—just silently fail the quest award.
    return;
}

return;
