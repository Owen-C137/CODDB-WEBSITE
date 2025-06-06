<?php
// inc/_global/ajax_followers.php

require __DIR__ . '/config.php';      // or wherever your PDO/$pdo is set up
require __DIR__ . '/login_check.php'; // so session_start() runs

// Only allow GET requests (to simplify caching behavior)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit;
}

$viewUserId = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
if ($viewUserId <= 0) {
    echo json_encode([]);
    exit;
}

// Fetch all followers for $viewUserId
$stmt = $pdo->prepare("
  SELECT
    f.follower_id AS id,
    u.username,
    COALESCE(up.profile_picture_url, u.profile_picture_url, '') AS avatar_url,
    COALESCE(r.name, '') AS role_name
  FROM `demon_follows` AS f
  JOIN `demon_users`        AS u  ON u.id      = f.follower_id
  LEFT JOIN `demon_user_profiles` AS up ON up.user_id = u.id
  LEFT JOIN `demon_roles`        AS r  ON r.id      = u.role_id
  WHERE f.user_id = :uid
  ORDER BY u.username
");
$stmt->execute(['uid' => $viewUserId]);
$followers = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($followers);
