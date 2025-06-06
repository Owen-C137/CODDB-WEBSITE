<?php
// inc/_global/quests.php
// ----------------------
//
// This file holds helper functions to “trigger”/“award” simple quests.
// Here we have enhanced the “complete_profile” quest so that, upon completion,
// we also insert a row into demon_notifications.

function tryCompleteProfileQuest(PDO $pdo, int $userId): void
{
    // 1) Look up the “complete_profile” quest definition
    $qStmt = $pdo->prepare("
        SELECT id, quest_key, reward_amount
        FROM demon_quests
        WHERE quest_key = 'complete_profile'
        LIMIT 1
    ");
    $qStmt->execute();
    $quest = $qStmt->fetch(PDO::FETCH_ASSOC);
    if (!$quest) {
        // If the quest isn't defined yet, bail.
        return;
    }
    $questId     = (int)$quest['id'];
    $questKey    = $quest['quest_key'];
    $rewardValue = (int)$quest['reward_amount'];

    // 2) Has the user already been marked completed?
    $uStmt = $pdo->prepare("
        SELECT is_completed
        FROM demon_user_quests
        WHERE user_id = :uid
          AND quest_id = :qid
        LIMIT 1
    ");
    $uStmt->execute([
        'uid' => $userId,
        'qid' => $questId
    ]);
    $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);
    if ($uRow && (int)$uRow['is_completed'] === 1) {
        // Already completed → nothing to do
        return;
    }

    // 3) Fetch the user’s profile to see if it's “complete”
    $pStmt = $pdo->prepare("
        SELECT
        COALESCE(up.first_name, '')            AS first_name,
        COALESCE(up.last_name, '')             AS last_name,
        COALESCE(up.profile_picture_url, '')   AS profile_picture_url,
        COALESCE(up.about_me, '')              AS about_me,
        COALESCE(up.date_of_birth, '')         AS date_of_birth,
        COALESCE(up.gender, '')                AS gender,
        COALESCE(up.cover_photo_url, '')       AS cover_photo_url
        FROM demon_user_profiles AS up
        WHERE up.user_id = :uid
        LIMIT 1
    ");
    $pStmt->execute(['uid' => $userId]);
    $prof = $pStmt->fetch(PDO::FETCH_ASSOC);

    // If no profile row exists or any required field is empty, bail.
    if (
        !$prof
        || trim($prof['first_name']) === ''
        || trim($prof['last_name']) === ''
        || trim($prof['profile_picture_url']) === ''
        || trim($prof['about_me']) === ''
        || trim($prof['date_of_birth']) === ''
        || trim($prof['gender']) === ''
        || trim($prof['cover_photo_url']) === ''
    ) {
        return;
    }

    // 4) User has a profile row, all required fields are non‐empty.
    //    Insert/update demon_user_quests, award credits, and now also notify.

    try {
        $pdo->beginTransaction();

        // a) Insert or update demon_user_quests
        if ($uRow) {
            // Row exists but not completed → mark it completed
            $upd = $pdo->prepare("
                UPDATE demon_user_quests
                SET quest_key      = :qkey,
                    progress_count = 1,
                    is_completed   = 1,
                    completed_at   = NOW(),
                    last_updated   = NOW()
                WHERE user_id  = :uid
                  AND quest_id = :qid
            ");
            $upd->execute([
                'uid'  => $userId,
                'qid'  => $questId,
                'qkey' => $questKey
            ]);
        } else {
            // No row → insert a new one
            $ins = $pdo->prepare("
                INSERT INTO demon_user_quests
                  (user_id, quest_key, quest_id, progress_count, is_completed, completed_at, last_updated)
                VALUES
                  (:uid, :qkey, :qid, 1, 1, NOW(), NOW())
            ");
            $ins->execute([
                'uid'  => $userId,
                'qkey' => $questKey,
                'qid'  => $questId
            ]);
        }

        // b) Insert a credit‐log (+rewardValue credits)
        $log = $pdo->prepare("
            INSERT INTO demon_credit_logs
              (user_id, change_amount, type, reason, description, created_at)
            VALUES
              (:uid, :amt, 'earn', 'Quest:complete_profile', 'Completed Profile Quest', NOW())
        ");
        $log->execute([
            'uid' => $userId,
            'amt' => $rewardValue
        ]);

        // c) Update the user’s credit_balance
        $updBal = $pdo->prepare("
            UPDATE demon_users
            SET credit_balance = credit_balance + :amt
            WHERE id = :uid
        ");
        $updBal->execute([
            'amt' => $rewardValue,
            'uid' => $userId
        ]);

        // d) INSERT a notification into demon_notifications
        $notif = $pdo->prepare("
            INSERT INTO demon_notifications
              (user_id, message, link, created_at)
            VALUES
              (:uid, :msg, :link, NOW())
        ");
        $notif->execute([
            'uid'  => $userId,
            'msg'  => "Congratulations! You completed your profile and earned {$rewardValue} credits.",
            'link' => 'credits.php'
        ]);

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("tryCompleteProfileQuest error for user {$userId}: " . $e->getMessage());
    }
}
