<?php
/**
 * backend/views/inc_sidebar.php
 *
 * Author: pixelcave
 *
 * The sidebar of each page
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure global config is loaded (so $site, $pdo, $cb exist)
if (!isset($site) || !isset($pdo) || !isset($cb)) {
    require_once __DIR__ . '/../../_global/config.php';
}

// Fetch current user info
$currentUsername = $_SESSION['username'] ?? 'Guest';
$profilePicUrl   = '';

// Only run this query if a user is actually logged in
if (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT
          COALESCE(up.profile_picture_url, u.profile_picture_url, '') AS profile_picture_url
        FROM `demon_users` AS u
        LEFT JOIN `demon_user_profiles` AS up ON up.user_id = u.id
        WHERE u.id = :uid
        LIMIT 1
    ");
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $profile = $stmt->fetch();
    $profilePicUrl = $profile['profile_picture_url'] ?? '';
}

// Compute cache-buster for avatar if available
$avatarCacheBuster = '';
if (!empty($profilePicUrl)) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $profilePicUrl;
    if (file_exists($fullPath)) {
        $avatarCacheBuster = filemtime($fullPath);
    } else {
        $avatarCacheBuster = time();
    }
}
?>

<!-- Sidebar -->
<nav id="sidebar">
  <!-- Sidebar Content -->
  <div class="sidebar-content">
    <!-- Side Header -->
    <div class="content-header justify-content-lg-center">
      <!-- Logo -->
      <div>
        <?php if (!empty($site['logo_url'])): ?>
          <a class="link-fx fw-bold tracking-wide mx-auto" href="/index.php">
            <img src="<?= htmlspecialchars($site['logo_url']); ?>"
                 alt="<?= htmlspecialchars($site['site_name']); ?>"
                 style="max-height: 32px;">
          </a>
        <?php else: ?>
          <a class="link-fx fw-bold tracking-wide mx-auto" href="/index.php">
            <span class="fs-4 text-dual"><?= htmlspecialchars($site['site_name']); ?></span>
          </a>
        <?php endif; ?>
      </div>
      <!-- END Logo -->

      <!-- Options -->
      <div>
        <!-- Close Sidebar, Visible only on mobile screens -->
        <button type="button" class="btn btn-sm btn-alt-danger d-lg-none"
                data-toggle="layout" data-action="sidebar_close">
          <i class="fa fa-fw fa-times"></i>
        </button>
        <!-- END Close Sidebar -->
      </div>
      <!-- END Options -->
    </div>
    <!-- END Side Header -->

    <!-- Sidebar Scrolling -->
    <div class="js-sidebar-scroll">
      <!-- Side User -->
      <div class="content-side content-side-user px-0 py-0">
        <!-- Visible only in mini mode -->
        <div class="smini-visible-block animated fadeIn px-3">
          <?php if (!empty($profilePicUrl)): ?>
            <img 
              src="<?= htmlspecialchars($profilePicUrl); ?>?v=<?= $avatarCacheBuster; ?>"
              alt="Avatar"
              class="img-avatar"
              style="width:32px; height:32px; object-fit:cover; border-radius:50%;"
            >
          <?php else: ?>
            <i class="fa fa-user fa-2x text-muted"></i>
          <?php endif; ?>
        </div>
        <!-- END Visible only in mini mode -->

        <!-- Visible only in normal mode -->
        <div class="smini-hidden text-center mx-auto">
          <a class="img-link" href="/profile.php">
            <?php if (!empty($profilePicUrl)): ?>
              <img 
                src="<?= htmlspecialchars($profilePicUrl); ?>?v=<?= $avatarCacheBuster; ?>"
                alt="Avatar"
                class="img-avatar"
                style="width:64px; height:64px; object-fit:cover; border-radius:50%;"
              >
            <?php else: ?>
              <i class="fa fa-user fa-3x text-muted"></i>
            <?php endif; ?>
          </a>
          <ul class="list-inline mt-3 mb-0">
            <li class="list-inline-item">
              <a class="link-fx text-dual fs-sm fw-semibold text-uppercase"
                 href="/profile.php">
                <?= htmlspecialchars($currentUsername); ?>
              </a>
            </li>
            <li class="list-inline-item">
              <a class="link-fx text-dual" data-toggle="layout"
                 data-action="dark_mode_toggle" href="javascript:void(0)">
                <i class="far fa-sun fa-fw opacity-50"></i>
              </a>
            </li>
            <li class="list-inline-item">
              <a class="link-fx text-dual" href="/logout.php">
                <i class="fa fa-sign-out-alt"></i>
              </a>
            </li>
          </ul>
        </div>
        <!-- END Visible only in normal mode -->
      </div>
      <!-- END Side User -->

      <!-- Side Navigation -->
      <div class="content-side content-side-full">
        <ul class="nav-main">
          <?php $cb->build_nav(); ?>
        </ul>
      </div>
      <!-- END Side Navigation -->
    </div>
    <!-- END Sidebar Scrolling -->
  </div>
  <!-- Sidebar Content -->
</nav>
<!-- END Sidebar -->
