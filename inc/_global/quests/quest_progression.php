<?php
// inc/_global/quests/quest_progression.php
// ---------------------------------------------------
//
// Contains a helper to build a full "$allQuests" array,
// merging demon_quests definitions with the current user's
// progress (so that 'is_completed', 'progress_pct', etc. exist.)
//

/**
 * Returns an associative array of all quests, keyed by quest_key.
 * Each entry contains:
 *   - id
 *   - title
 *   - description
 *   - reward_amount
 *   - is_repeatable
 *   - threshold_count   ← how many “units” are required (pulled from DB)
 *   - is_completed      (boolean)
 *   - progress_pct      (0–100)
 *   - long_description
 *   - progress_count    (raw count for partial display if desired)
 *
 * @param PDO $pdo
 * @param int $userId
 * @return array
 */
function buildAllQuests(PDO $pdo, int $userId): array
{
    $allQuests = [];

    // 1) Fetch all quest definitions (including threshold_count)
    $stmt = $pdo->query("
        SELECT
          id,
          quest_key,
          name         AS title,
          description,
          reward_amount,
          is_repeatable,
          threshold_count
        FROM demon_quests
        ORDER BY id ASC
    ");
    $quests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$quests) {
        return [];
    }

    // 2) Collect quest IDs and fetch any existing user progress rows
    $questIds   = array_column($quests, 'id');
    $userQuests = [];
    if (count($questIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($questIds), '?'));
        $uStmt = $pdo->prepare("
            SELECT 
              quest_id,
              progress_count,
              is_completed
            FROM demon_user_quests
            WHERE user_id = ?
              AND quest_id IN ($placeholders)
        ");
        $params = array_merge([$userId], $questIds);
        $uStmt->execute($params);
        $rows = $uStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $userQuests[(int)$r['quest_id']] = [
                'progress_count' => (int)$r['progress_count'],
                'is_completed'   => (int)$r['is_completed'],
            ];
        }
    }

    // 3) Build the final array
    foreach ($quests as $q) {
        $qid            = (int)$q['id'];
        $qkey           = $q['quest_key'];
        $title          = $q['title'];
        $desc           = $q['description'];
        $reward         = (int)$q['reward_amount'];
        $repeat         = (int)$q['is_repeatable'];
        $thresholdCount = max(1, (int)$q['threshold_count']); // always ≥1

        // Determine raw progress_count and completion status:
        $isDone       = false;
        $userProgress = 0;

        if (isset($userQuests[$qid])) {
            // Quest has been started or completed at least once
            $userProgress = $userQuests[$qid]['progress_count'];
            if ($userQuests[$qid]['is_completed'] === 1) {
                $isDone = true;
            }
        }

        // If this is "follow_5_users" or "be_followed_10" and not yet completed,
        // override $userProgress with the live count from demon_follows:
        if (! $isDone) {
            if ($qkey === 'follow_5_users') {
                // Count how many people this user is following
                $cntStmt = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM demon_follows 
                    WHERE follower_id = :uid
                ");
                $cntStmt->execute(['uid' => $userId]);
                $userProgress = (int) $cntStmt->fetchColumn();
            }
            elseif ($qkey === 'be_followed_10') {
                // Count how many people are following this user
                $cntStmt = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM demon_follows 
                    WHERE user_id = :uid
                ");
                $cntStmt->execute(['uid' => $userId]);
                $userProgress = (int) $cntStmt->fetchColumn();
            }
        }

        // Calculate progress_pct:
        if ($isDone) {
            $pct = 100;
        } else {
            $pct = (int) floor(($userProgress / $thresholdCount) * 100);
            if ($pct > 100) {
                $pct = 100;
            }
        }

        // long_description: reuse description for now (you can customize per-quest later)
        $longDesc = $desc;

        $allQuests[$qkey] = [
            'id'               => $qid,
            'title'            => $title,
            'description'      => $desc,
            'reward_amount'    => $reward,
            'is_repeatable'    => $repeat,
            'threshold_count'  => $thresholdCount,
            'is_completed'     => $isDone,
            'progress_pct'     => $pct,
            'long_description' => $longDesc,
            'progress_count'   => $userProgress,
        ];
    }

    return $allQuests;
}
