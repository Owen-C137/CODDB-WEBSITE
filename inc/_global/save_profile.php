<?php
// inc/_global/save_profile.php

// 1) Bootstrap (no HTML/templates here)
require __DIR__ . '/config.php';         // loads $pdo, $cb, $site, etc.
require __DIR__ . '/login_check.php';    // starts session and checks $_SESSION['user_id']

// Force JSON output
header('Content-Type: application/json; charset=UTF-8');

// 2) Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// 3) Must be logged in
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
if ($currentUserId <= 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

// 4) CSRF validation
$csrfToken = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

// 5) Collect text inputs
$firstName       = trim($_POST['first_name'] ?? '');
$lastName        = trim($_POST['last_name'] ?? '');
$aboutMe         = trim($_POST['about_me'] ?? '');
$dateOfBirth     = trim($_POST['date_of_birth'] ?? '') ?: null;
$gender          = trim($_POST['gender'] ?? '');
$city            = trim($_POST['city'] ?? '');
$country         = trim($_POST['country'] ?? '');
$discordUsername = trim($_POST['discord_username'] ?? '');
$websiteUrl      = trim($_POST['website_url'] ?? '');
$steamUrl        = trim($_POST['steam_profile_url'] ?? '');
$githubUsername  = trim($_POST['github_username'] ?? '');
$twitterHandle   = trim($_POST['twitter_handle'] ?? '');
$isPublic        = isset($_POST['is_public']) && $_POST['is_public'] === '1' ? 1 : 0;
$followersOnly   = isset($_POST['followers_only']) && $_POST['followers_only'] === '1' ? 1 : 0;

// 6) Handle Avatar upload vs. URL
$avatarUrl = trim($_POST['profile_picture_url'] ?? '');
if (!empty($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
    $fileTmp  = $_FILES['avatar_file']['tmp_name'];
    $fileName = $_FILES['avatar_file']['name'];
    $fileType = mime_content_type($fileTmp);

    // Only allow images
    $allowedTypes = ['image/jpeg','image/png','image/gif','image/webp'];
    if (!in_array($fileType, $allowedTypes, true)) {
        echo json_encode(['success' => false, 'message' => 'Avatar must be a JPEG, PNG, GIF or WEBP.']);
        exit;
    }

    // Generate unique filename
    $ext     = pathinfo($fileName, PATHINFO_EXTENSION);
    $newName = 'avatar_' . $currentUserId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destDir = __DIR__ . '/../../assets/media/avatars/';
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    $destPath = $destDir . $newName;

    if (!move_uploaded_file($fileTmp, $destPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save avatar.']);
        exit;
    }

    // Overwrite with new relative URL
    $avatarUrl = $cb->assets_folder . '/media/avatars/' . $newName;
}

// 7) Handle Cover upload vs. URL
$coverUrl = trim($_POST['cover_photo_url'] ?? '');
if (!empty($_FILES['cover_file']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
    $fileTmp  = $_FILES['cover_file']['tmp_name'];
    $fileName = $_FILES['cover_file']['name'];
    $fileType = mime_content_type($fileTmp);

    // Only allow images
    $allowedTypes = ['image/jpeg','image/png','image/gif','image/webp'];
    if (!in_array($fileType, $allowedTypes, true)) {
        echo json_encode(['success' => false, 'message' => 'Cover photo must be a JPEG, PNG, GIF or WEBP.']);
        exit;
    }

    // Generate unique filename
    $ext     = pathinfo($fileName, PATHINFO_EXTENSION);
    $newName = 'cover_' . $currentUserId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destDir = __DIR__ . '/../../assets/media/covers/';
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    $destPath = $destDir . $newName;

    if (!move_uploaded_file($fileTmp, $destPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save cover photo.']);
        exit;
    }

    // Overwrite with new relative URL
    $coverUrl = $cb->assets_folder . '/media/covers/' . $newName;
}

// 8) Upsert into demon_user_profiles
$sql = "
    INSERT INTO `demon_user_profiles` (
        user_id,
        first_name,
        last_name,
        about_me,
        date_of_birth,
        gender,
        city,
        country,
        discord_username,
        website_url,
        steam_profile_url,
        github_username,
        twitter_handle,
        profile_picture_url,
        cover_photo_url,
        is_public,
        followers_only
    ) VALUES (
        :user_id,
        :first_name,
        :last_name,
        :about_me,
        :date_of_birth,
        :gender,
        :city,
        :country,
        :discord_username,
        :website_url,
        :steam_profile_url,
        :github_username,
        :twitter_handle,
        :profile_picture_url,
        :cover_photo_url,
        :is_public,
        :followers_only
    )
    ON DUPLICATE KEY UPDATE
        first_name           = VALUES(first_name),
        last_name            = VALUES(last_name),
        about_me             = VALUES(about_me),
        date_of_birth        = VALUES(date_of_birth),
        gender               = VALUES(gender),
        city                 = VALUES(city),
        country              = VALUES(country),
        discord_username     = VALUES(discord_username),
        website_url          = VALUES(website_url),
        steam_profile_url    = VALUES(steam_profile_url),
        github_username      = VALUES(github_username),
        twitter_handle       = VALUES(twitter_handle),
        profile_picture_url  = VALUES(profile_picture_url),
        cover_photo_url      = VALUES(cover_photo_url),
        is_public            = VALUES(is_public),
        followers_only       = VALUES(followers_only)
";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        'user_id'             => $currentUserId,
        'first_name'          => $firstName,
        'last_name'           => $lastName,
        'about_me'            => $aboutMe,
        'date_of_birth'       => $dateOfBirth,
        'gender'              => $gender,
        'city'                => $city,
        'country'             => $country,
        'discord_username'    => $discordUsername,
        'website_url'         => $websiteUrl,
        'steam_profile_url'   => $steamUrl,
        'github_username'     => $githubUsername,
        'twitter_handle'      => $twitterHandle,
        'profile_picture_url' => $avatarUrl,
        'cover_photo_url'     => $coverUrl,
        'is_public'           => $isPublic,
        'followers_only'      => $followersOnly
    ]);

    // ── FIXED PATH: point to quests/index.php correctly ──
    require_once __DIR__ . '/quests/index.php';
    tryCompleteProfileQuest($pdo, $currentUserId);
    // ─────────────────────────────────────────────────────

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error.']);
    exit;
}
