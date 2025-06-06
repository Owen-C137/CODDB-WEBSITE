<?php
// inc/_global/quests/chat_50_times.php
// ---------------------------------------------------
//
// Tracks “chat_50_times” quest progress. Call this
// function whenever a user successfully posts a chat message.
//
// When the user has posted `threshold_count` messages, it marks
// the quest complete, awards credits, updates the user’s balance,
// and sends a notification.
//

function recordChat50Progress(PDO $pdo, int $userId): void
{
    // 1) Look up the “chat_50_times” quest definition, including its threshold_count
    $qStmt = $pdo->prepare(
        'SELECT id, quest_key, reward_amount, threshold_count
         FROM demon_quests
         WHERE quest_key = :qkey
         LIMIT 1'
    );
    $qStmt->execute(['qkey' => 'chat_50_times']);
    $quest = $qStmt->fetch(PDO::FETCH_ASSOC);
    if (!$quest) {
        // Quest not defined yet
        return;
    }

    $questId        = (int)$quest['id'];
    $questKey       = $quest['quest_key'];
    $rewardValue    = (int)$quest['reward_amount'];
    // Use the database‐stored threshold (defaulted to 50 if you haven’t changed it)
    $thresholdCount = max(1, (int)$quest['threshold_count']);

    // 2) Fetch any existing progress row for this user & quest
    $uStmt = $pdo->prepare(
        'SELECT progress_count, is_completed
         FROM demon_user_quests
         WHERE user_id  = :uid
           AND quest_id = :qid
         LIMIT 1'
    );
    $uStmt->execute([
        'uid' => $userId,
        'qid' => $questId
    ]);
    $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);

    if ($uRow) {
        // If already completed, do nothing
        if ((int)$uRow['is_completed'] === 1) {
            return;
        }

        // Increment progress_count by 1
        $newCount = (int)$uRow['progress_count'] + 1;
        // Compare against thresholdCount (instead of hard-coded 50)
        $isDone   = ($newCount >= $thresholdCount) ? 1 : 0;

        // Build partial SQL for setting completed_at only when done
        $completedAtSql = $isDone
            ? 'completed_at = NOW(),'
            : ''; // leave completed_at unchanged if not done

        try {
            $pdo->beginTransaction();

            // 3) Update the user_quests row
            $updSql = "
                UPDATE demon_user_quests
                SET
                    progress_count = :pc,
                    is_completed   = :ic,
                    $completedAtSql
                    last_updated   = NOW()
                WHERE user_id  = :uid
                  AND quest_id = :qid
            ";
            $upd = $pdo->prepare($updSql);
            $upd->execute([
                'pc'  => $newCount,
                'ic'  => $isDone,
                'uid' => $userId,
                'qid' => $questId
            ]);

            if ($isDone) {
                // 4) Quest just completed: award credits + send notification

                // a) Insert into demon_credit_logs
                $log = $pdo->prepare(
                    'INSERT INTO demon_credit_logs
                      (user_id, change_amount, type, reason, description, created_at)
                     VALUES
                      (:uid, :amt, \'earn\', :reason, :description, NOW())'
                );
                $log->execute([
                    'uid'         => $userId,
                    'amt'         => $rewardValue,
                    'reason'      => 'Quest:chat_50_times',
                    'description' => "Sent {$thresholdCount} chat messages"
                ]);

                // b) Update user's credit_balance
                $updBal = $pdo->prepare(
                    'UPDATE demon_users
                     SET credit_balance = credit_balance + :amt
                     WHERE id = :uid'
                );
                $updBal->execute([
                    'amt' => $rewardValue,
                    'uid' => $userId
                ]);

                // c) Send a notification
                $message = "Congrats! You’ve sent {$thresholdCount} messages and earned {$rewardValue} credits.";
                $notif   = $pdo->prepare(
                    'INSERT INTO demon_notifications
                      (user_id, message, link, created_at)
                     VALUES
                      (:uid, :msg, :link, NOW())'
                );
                $notif->execute([
                    'uid'  => $userId,
                    'msg'  => $message,
                    'link' => 'credits.php'
                ]);
            }

            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("recordChat50Progress error for user {$userId}: " . $e->getMessage());
        }
    } else {
        // No existing row → insert with progress_count = 1 (not yet completed)
        try {
            $pdo->beginTransaction();

            $ins = $pdo->prepare(
                'INSERT INTO demon_user_quests
                  (user_id, quest_key, quest_id, progress_count, is_completed, last_updated)
                 VALUES
                  (:uid, :qkey, :qid, 1, 0, NOW())'
            );
            $ins->execute([
                'uid'  => $userId,
                'qkey' => $questKey,
                'qid'  => $questId
            ]);

            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("recordChat50Progress insert error for user {$userId}: " . $e->getMessage());
        }
    }
}
