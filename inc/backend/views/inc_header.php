<!-- Put this near the top of your page (once), pointing at your sound file -->
<audio id="notif-sound" src="/assets/media/sounds/notifications/new-notification-10-352755.mp3" preload="auto"></audio>
<?php
/**
 * backend/views/inc_header.php
 *
 * The header of each page, now with inline “mark as read” / “mark all as read” support,
 * plus session timeout handling.
 */


// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─────────────────────────────────────────────────────────────────────────────
// Session timeout: if last activity was more than 30 minutes ago, force logout
// ─────────────────────────────────────────────────────────────────────────────
$timeoutSeconds = 1800; // 30 minutes

if (isset($_SESSION['LAST_ACTIVITY'])) {
    $elapsed = time() - $_SESSION['LAST_ACTIVITY'];
    if ($elapsed > $timeoutSeconds) {
        // Destroy session and redirect to login
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        header('Location: /login.php');
        exit;
    }
}
// Update last activity timestamp
$_SESSION['LAST_ACTIVITY'] = time();

// ─────────────────────────────────────────────────────────────────────────────
// Ensure global config is loaded (so $site, $pdo, $cb exist)
// ─────────────────────────────────────────────────────────────────────────────
if (!isset($site) || !isset($pdo) || !isset($cb)) {
    /**
     * If this file is being included directly, $site and $pdo might not be set yet.
     * We load the global config to guarantee $pdo (and $site, $cb, etc.) exist.
     */
    require_once __DIR__ . '/../../_global/config.php';
}

// ─────────────────────────────────────────────────────────────────────────────
// 1) “Mark as read” handler: if ?mark_read=ID is present, update that notification
// ─────────────────────────────────────────────────────────────────────────────
if (isset($_SESSION['user_id'], $_GET['mark_read'])) {
    $notifId = (int)$_GET['mark_read'];
    $userId  = $_SESSION['user_id'];

    $update = $pdo->prepare("
        UPDATE `demon_notifications`
        SET is_read = 1
        WHERE id = :nid
          AND user_id = :uid
    ");
    $update->execute([
        'nid' => $notifId,
        'uid' => $userId
    ]);
}

// ─────────────────────────────────────────────────────────────────────────────
// 1b) “Mark all as read” handler: if ?mark_read_all=1 is present, update all unread
// ─────────────────────────────────────────────────────────────────────────────
if (isset($_SESSION['user_id'], $_GET['mark_read_all'])) {
    $userId = $_SESSION['user_id'];

    $updateAll = $pdo->prepare("
        UPDATE `demon_notifications`
        SET is_read = 1
        WHERE user_id = :uid
          AND is_read = 0
    ");
    $updateAll->execute(['uid' => $userId]);

    $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $baseUrl);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// 2) Fetch current user info (including joined profile table if available)
// ─────────────────────────────────────────────────────────────────────────────
$currentUserId   = $_SESSION['user_id']   ?? null;
$currentUsername = $_SESSION['username']   ?? 'Guest';
$profilePicUrl   = '';

// ─────────────────────────────────────────────────────────────────────────────
// (2b) If logged in, bump their last_activity timestamp
// ─────────────────────────────────────────────────────────────────────────────
if ($currentUserId) {
    $upd = $pdo->prepare("
        UPDATE `demon_users`
        SET last_activity = NOW()
        WHERE id = :uid
    ");
    $upd->execute(['uid' => $currentUserId]);
}


if ($currentUserId) {
    // Join demon_user_profiles first, fallback to demon_users.profile_picture_url
    $stmt = $pdo->prepare("
        SELECT
          COALESCE(up.profile_picture_url, u.profile_picture_url, '') AS profile_picture_url
        FROM `demon_users` AS u
        LEFT JOIN `demon_user_profiles` AS up ON up.user_id = u.id
        WHERE u.id = :uid
        LIMIT 1
    ");
    $stmt->execute(['uid' => $currentUserId]);
    $profile = $stmt->fetch();
    $profilePicUrl = $profile['profile_picture_url'] ?? '';
}

// ─────────────────────────────────────────────────────────────────────────────
// 2b) Compute cache-buster for avatar if available
// ─────────────────────────────────────────────────────────────────────────────
$avatarCacheBuster = '';
if (!empty($profilePicUrl)) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $profilePicUrl;
    if (file_exists($fullPath)) {
        $avatarCacheBuster = filemtime($fullPath);
    } else {
        $avatarCacheBuster = time();
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// 3) Fetch notifications for logged-in user
// ─────────────────────────────────────────────────────────────────────────────
$unreadCount   = 0;
$notifications = [];

if ($currentUserId) {
    // 3a) Count unread
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) AS cnt
        FROM `demon_notifications`
        WHERE user_id = :uid
          AND is_read = 0
    ");
    $countStmt->execute(['uid' => $currentUserId]);
    $row = $countStmt->fetch();
    $unreadCount = (int)$row['cnt'];

    // 3b) Fetch latest 5 notifications
    $notifStmt = $pdo->prepare("
        SELECT id, message, link, is_read, created_at
        FROM `demon_notifications`
        WHERE user_id = :uid
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $notifStmt->execute(['uid' => $currentUserId]);
    $notifications = $notifStmt->fetchAll();
}

$userCreditBalance = 0; // default if something goes wrong

if ($currentUserId > 0) {
    $balStmt = $pdo->prepare("
        SELECT credit_balance
        FROM `demon_users`
        WHERE id = :uid
        LIMIT 1
    ");
    $balStmt->execute(['uid' => $currentUserId]);
    $userCreditBalance = (int)$balStmt->fetchColumn();
}


?>

<!-- Header -->
<header id="page-header">
  <!-- Header Content -->
  <div class="content-header">
    <!-- Left Section -->
    <div class="space-x-1">
      <!-- Toggle Sidebar -->
      <button type="button" class="btn btn-sm btn-alt-secondary"
              data-toggle="layout" data-action="sidebar_toggle">
        <i class="fa fa-fw fa-bars"></i>
      </button>
      <!-- END Toggle Sidebar -->

      <!-- Site Logo or Name -->
      <?php if (!empty($site['logo_url'])): ?>
        <a href="<?= htmlspecialchars($site['site_url']); ?>"
           class="btn btn-sm btn-alt-secondary ms-2">
          <img src="<?= htmlspecialchars($site['logo_url']); ?>"
               alt="<?= htmlspecialchars($site['site_name']); ?>"
               style="max-height: 24px;">
        </a>
      <?php else: ?>
        <a href="<?= htmlspecialchars($site['site_url']); ?>"
           class="btn btn-sm btn-alt-secondary ms-2">
          <?= htmlspecialchars($site['site_name']); ?>
        </a>
      <?php endif; ?>
      <!-- END Site Logo or Name -->

      <!-- Open Search Section -->
      <button type="button" class="btn btn-sm btn-alt-secondary"
              data-toggle="layout" data-action="header_search_on">
        <i class="fa fa-fw fa-search"></i>
      </button>
      <!-- END Open Search Section -->

      <!-- Color Themes Dropdown -->
      <div class="dropdown d-inline-block">
        <button type="button" class="btn btn-sm btn-alt-secondary"
                id="page-header-themes-dropdown" data-bs-toggle="dropdown"
                data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
          <i class="fa fa-fw fa-brush"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-lg p-0"
             aria-labelledby="page-header-themes-dropdown">
          <div class="px-3 py-2 bg-body-light rounded-top">
            <h5 class="fs-sm text-center mb-0">Color Themes</h5>
          </div>
          <div class="p-3">
            <div class="row g-0 text-center">
              <div class="col-2">
                <a class="text-default" data-toggle="theme"
                   data-theme="default" href="javascript:void(0)">
                  <i class="fa fa-2x fa-circle"></i>
                </a>
              </div>
              <div class="col-2">
                <a class="text-elegance" data-toggle="theme"
                   data-theme="<?= $cb->assets_folder; ?>/css/themes/elegance.min.css"
                   href="javascript:void(0)">
                  <i class="fa fa-2x fa-circle"></i>
                </a>
              </div>
              <div class="col-2">
                <a class="text-pulse" data-toggle="theme"
                   data-theme="<?= $cb->assets_folder; ?>/css/themes/pulse.min.css"
                   href="javascript:void(0)">
                  <i class="fa fa-2x fa-circle"></i>
                </a>
              </div>
              <div class="col-2">
                <a class="text-flat" data-toggle="theme"
                   data-theme="<?= $cb->assets_folder; ?>/css/themes/flat.min.css"
                   href="javascript:void(0)">
                  <i class="fa fa-2x fa-circle"></i>
                </a>
              </div>
              <div class="col-2">
                <a class="text-corporate" data-toggle="theme"
                   data-theme="<?= $cb->assets_folder; ?>/css/themes/corporate.min.css"
                   href="javascript:void(0)">
                  <i class="fa fa-2x fa-circle"></i>
                </a>
              </div>
              <div class="col-2">
                <a class="text-earth" data-toggle="theme"
                   data-theme="<?= $cb->assets_folder; ?>/css/themes/earth.min.css"
                   href="javascript:void(0)">
                  <i class="fa fa-2x fa-circle"></i>
                </a>
              </div>
            </div>
          </div>
          <div class="px-3 py-2 bg-body-light rounded-top">
            <h5 class="fs-sm text-center mb-0">Dark Mode</h5>
          </div>
          <div class="px-2 py-3">
            <div class="row g-1 text-center">
              <div class="col-4">
                <button type="button"
                        class="dropdown-item mb-0 d-flex align-items-center gap-2"
                        data-toggle="layout" data-action="dark_mode_off"
                        data-dark-mode="off">
                  <i class="far fa-sun fa-fw opacity-50"></i>
                  <span class="fs-sm fw-medium">Light</span>
                </button>
              </div>
              <div class="col-4">
                <button type="button"
                        class="dropdown-item mb-0 d-flex align-items-center gap-2"
                        data-toggle="layout" data-action="dark_mode_on"
                        data-dark-mode="on">
                  <i class="fa fa-moon fa-fw opacity-50"></i>
                  <span class="fs-sm fw-medium">Dark</span>
                </button>
              </div>
              <div class="col-4">
                <button type="button"
                        class="dropdown-item mb-0 d-flex align-items-center gap-2"
                        data-toggle="layout" data-action="dark_mode_system"
                        data-dark-mode="system">
                  <i class="fa fa-desktop fa-fw opacity-50"></i>
                  <span class="fs-sm fw-medium">System</span>
                </button>
              </div>
            </div>
          </div>
          <div class="p-3 bg-body-light rounded-bottom">
            <div class="row g-sm text-center">
              <div class="col-6">
                <a class="dropdown-item fs-sm fw-medium mb-0" href="be_layout_api.php">
                  <i class="fa fa-flask opacity-50 me-1"></i> Layout API
                </a>
              </div>
              <div class="col-6">
                <a class="dropdown-item fs-sm fw-medium mb-0" href="be_ui_color_themes.php">
                  <i class="fa fa-paint-brush opacity-50 me-1"></i> Themes
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- END Color Themes -->
    </div>
    <!-- END Left Section -->

    <!-- Right Section -->
    <div class="space-x-1">
      <!-- User Dropdown -->
      <div class="dropdown d-inline-block">
        <button type="button" class="btn btn-sm btn-alt-secondary"
                id="page-header-user-dropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <?php if (!empty($profilePicUrl)): ?>
            <img 
              src="<?= htmlspecialchars($profilePicUrl); ?>?v=<?= $avatarCacheBuster; ?>" 
              alt="Avatar"
              class="rounded-circle"
              style="width:24px; height:24px; object-fit:cover;"
            >
          <?php else: ?>
            <i class="fa fa-user d-sm-none"></i>
          <?php endif; ?>
          <span class="d-none d-sm-inline-block fw-semibold ms-1">
            <?= htmlspecialchars($currentUsername); ?>
          </span>
          <i class="fa fa-angle-down opacity-50 ms-1"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-md dropdown-menu-end p-0"
             aria-labelledby="page-header-user-dropdown">
          <div class="px-2 py-3 bg-body-light rounded-top text-center">
            <?php if (!empty($profilePicUrl)): ?>
              <img 
                src="<?= htmlspecialchars($profilePicUrl); ?>?v=<?= $avatarCacheBuster; ?>" 
                alt="Avatar"
                class="rounded-circle mb-2"
                style="width:48px; height:48px; object-fit:cover;"
              >
            <?php else: ?>
              <i class="fa fa-user fa-2x opacity-50 mb-2"></i>
            <?php endif; ?>
            <h5 class="h6 mb-0"><?= htmlspecialchars($currentUsername); ?></h5>
          </div>
          <div class="p-2">
            <a class="dropdown-item d-flex align-items-center justify-content-between space-x-1"
               href="/profile.php">
              <span>Profile</span>
              <i class="fa fa-fw fa-user opacity-25"></i>
            </a>
            <a class="dropdown-item d-flex align-items-center justify-content-between"
               href="/inbox.php">
              <span>Inbox</span>
              <i class="fa fa-fw fa-envelope-open opacity-25"></i>
            </a>
            <a class="dropdown-item d-flex align-items-center justify-content-between space-x-1"
               href="/invoices.php">
              <span>Invoices</span>
              <i class="fa fa-fw fa-file opacity-25"></i>
            </a>
            <div class="dropdown-divider"></div>

            <!-- Side Overlay / Settings -->
            <a class="dropdown-item d-flex align-items-center justify-content-between space-x-1"
               href="javascript:void(0)"
               data-toggle="layout"
               data-action="side_overlay_toggle">
              <span>Settings</span>
              <i class="fa fa-fw fa-wrench opacity-25"></i>
            </a>
            <!-- END Side Overlay -->

            <div class="dropdown-divider"></div>
            <a class="dropdown-item d-flex align-items-center justify-content-between space-x-1"
               href="/logout.php">
              <span>Sign Out</span>
              <i class="fa fa-fw fa-sign-out-alt opacity-25"></i>
            </a>
          </div>
        </div>
      </div>
      <!-- END User Dropdown -->
      <!-- Start Credits -->
    <?php if ($currentUserId): 
        // Fetch credit balance
        $balStmt = $pdo->prepare("
          SELECT credit_balance 
          FROM `demon_users` 
          WHERE id = :uid 
          LIMIT 1
        ");
        $balStmt->execute(['uid' => $currentUserId]);
        $row = $balStmt->fetch(PDO::FETCH_ASSOC);
        $creditBalance = (int)($row['credit_balance'] ?? 0);
    ?>
      <div class="d-inline-block me-2">
        <a href="/credits.php" class="btn btn-sm btn-alt-secondary">
          <i class="fa fa-coins me-1"></i>
          Credits: 
          <span id="header-credit-balance"><?= htmlspecialchars($creditBalance); ?></span>
        </a>
      </div>
    <?php endif; ?>
      <!-- End Credits -->
      <!-- Notifications -->
      <div class="dropdown d-inline-block" data-bs-auto-close="outside">
        <button type="button" class="btn btn-sm btn-alt-secondary"
                id="page-header-notifications" data-bs-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
          <i class="fa fa-bell"></i>
          <span class="badge bg-primary rounded-pill" id="notif-badge" style="display: none;">0</span>
        </button>

        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
             aria-labelledby="page-header-notifications">
          <div class="px-2 py-3 bg-body-light rounded-top">
            <h5 class="h6 text-center mb-0">Notifications</h5>
          </div>
          <ul class="nav-items my-2 fs-sm" id="notif-list">
            <li>
              <div class="text-center text-muted py-2">Loading…</div>
            </li>
          </ul>
          <div class="p-2 bg-body-light rounded-bottom text-center">
            <a class="dropdown-item fs-sm fw-medium mb-1"
               href="/notifications.php">
              <i class="fa fa-fw fa-flag opacity-50 me-1"></i> View All
            </a>
            <button type="button"
                    class="dropdown-item fs-sm fw-medium mb-0"
                    id="mark-all-read">
              <i class="fa fa-fw fa-check opacity-50 me-1"></i> Mark All as Read
            </button>
          </div>
        </div>
      </div>
      <!-- END Notifications -->

      <!-- Toggle Side Overlay -->
      <button type="button" class="btn btn-sm btn-alt-secondary"
              data-toggle="layout" data-action="side_overlay_toggle">
        <i class="fa fa-fw fa-stream"></i>
      </button>
      <!-- END Toggle Side Overlay -->
    </div>
    <!-- END Right Section -->
  </div>
  <!-- END Header Content -->

  <!-- Header Search -->
  <div id="page-header-search" class="overlay-header bg-body-extra-light">
    <div class="content-header">
      <form class="w-100" action="/search.php" method="POST">
        <div class="input-group">
          <button type="button" class="btn btn-secondary"
                  data-toggle="layout" data-action="header_search_off">
            <i class="fa fa-fw fa-times"></i>
          </button>
          <input type="text" class="form-control"
                 placeholder="Search or hit ESC.."
                 id="page-header-search-input"
                 name="page-header-search-input">
          <button type="submit" class="btn btn-secondary">
            <i class="fa fa-fw fa-search"></i>
          </button>
        </div>
      </form>
    </div>
  </div>
  <!-- END Header Search -->

  <!-- Header Loader -->
  <div id="page-header-loader" class="overlay-header bg-primary">
    <div class="content-header">
      <div class="w-100 text-center">
        <i class="far fa-sun fa-spin text-white"></i>
      </div>
    </div>
  </div>
  <!-- END Header Loader -->
</header>
<!-- END Header -->

<script>
document.addEventListener('DOMContentLoaded', function() {
  // 1) Grab the audio element
  const notifSound = document.getElementById('notif-sound');

  // 2) Track the last known unread count
  let lastUnreadCount = 0;

  // 3) Function to fetch & refresh notifications
  function fetchNotifications() {
    fetch('/inc/_global/ajax_get_notifications.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({}) 
    })
    .then(res => res.json())
    .then(data => {
      if (!data.success) {
        console.warn('Failed to fetch notifications:', data.error);
        return;
      }

      // 3a) If we already had some unread (lastUnreadCount > 0)
      //     and now data.unread_count is strictly larger, play sound.
      if (data.unread_count > lastUnreadCount) {
        notifSound.currentTime = 0;
        notifSound.play().catch(()=>{
          // maybe user hasn’t interacted yet, so autoplay may be blocked.
        });
      }

      // 3b) Update lastUnreadCount BEFORE updating the badge
      lastUnreadCount = data.unread_count;

      // 3c) Show/hide the badge immediately whenever unread_count changes:
      const badge = document.getElementById('notif-badge');
      if (badge) {
        if (data.unread_count > 0) {
          badge.textContent = data.unread_count;
          badge.style.display = ''; // show badge
        } else {
          badge.style.display = 'none'; // hide badge
        }
      }

      // 3d) Rebuild the notification list exactly as before...
      const list = document.getElementById('notif-list');
      if (list) {
        list.innerHTML = '';
        if (data.notifications.length === 0) {
          list.innerHTML = `
            <li>
              <div class="text-center text-muted py-2">
                No notifications
              </div>
            </li>`;
        } else {
          data.notifications.forEach(notif => {
            const iconHtml = notif.is_read == 0
                              ? '<i class="fa fa-circle text-primary fa-xs"></i>'
                              : '<i class="fa fa-circle text-muted fa-xs"></i>';
            const link = notif.link || '/notifications.php';
            const readBtn = notif.is_read == 0
              ? `<button type="button" class="btn p-0 text-muted mark-read-single" data-id="${notif.id}" title="Mark as read">
                   <i class="fa fa-check"></i>
                 </button>`
              : '';
            const item = document.createElement('li');
            item.className = 'd-flex align-items-start py-2 px-3';
            item.setAttribute('data-id', notif.id);
            item.innerHTML = `
              <div class="flex-shrink-0 me-2">
                ${iconHtml}
              </div>
              <div class="flex-grow-1 pe-2">
                <a class="text-dark" href="${link}">
                  <p class="fw-medium mb-1">
                    ${notif.message.replace(/</g, '&lt;').replace(/>/g, '&gt;')}
                  </p>
                </a>
                <div class="text-muted">
                  ${new Date(notif.created_at).toLocaleString('en-GB', {
                    day: 'numeric', month: 'short', year: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                  })}
                </div>
              </div>
              <div class="flex-shrink-0 ms-auto">
                ${readBtn}
              </div>`;
            list.appendChild(item);
          });
        }
      }

      // 3e) Re-attach the “mark as read” handlers
      attachMarkReadHandlers();
    })
    .catch(err => {
      console.error('Ajax error fetching notifications:', err);
    });
  }

  // 4) Initial fetch on page load
  fetchNotifications();

  // 5) Poll every 15 seconds
  setInterval(fetchNotifications, 15000);

  // 6) Same “mark as read” logic as before:
  function attachMarkReadHandlers() {
    document.querySelectorAll('.mark-read-single').forEach(btn => {
      btn.onclick = function(e) {
        e.preventDefault();
        const nid = this.getAttribute('data-id');
        if (!nid) return;

        fetch('/inc/_global/mark_notification.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ action: 'single', notif_id: nid })
        })
        .then(res => res.json())
        .then(resp => {
          if (resp.success) {
            // Grey out icon + remove check-button
            const icon = this.closest('li').querySelector('i');
            icon.classList.remove('text-primary');
            icon.classList.add('text-muted');
            this.remove();

            // Decrement badge count right away
            lastUnreadCount = Math.max(0, lastUnreadCount - 1);
            const badge = document.getElementById('notif-badge');
            if (badge) {
              if (lastUnreadCount > 0) {
                badge.textContent = lastUnreadCount;
              } else {
                badge.style.display = 'none';
              }
            }
          } else {
            alert(resp.error || 'Unable to mark as read.');
          }
        })
        .catch(err => console.error(err));
      };
    });

    const markAllBtn = document.getElementById('mark-all-read');
    if (markAllBtn) {
      markAllBtn.onclick = function(e) {
        e.preventDefault();
        fetch('/inc/_global/mark_notification.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ action: 'all' })
        })
        .then(res => res.json())
        .then(resp => {
          if (resp.success) {
            // Clear everything in one go
            lastUnreadCount = 0;
            const badge = document.getElementById('notif-badge');
            if (badge) badge.style.display = 'none';
            fetchNotifications();
          } else {
            alert(resp.error || 'Unable to mark all as read.');
          }
        })
        .catch(err => console.error(err));
      };
    }
  }

  // 7) Initial “mark as read” attachment (if any static items exist)
  attachMarkReadHandlers();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // … (your existing notification‐polling code) …

  // ==== New: poll every 60 seconds to refresh “Credits” in the header ====
  function fetchCreditBalance() {
    fetch('/inc/_global/ajax_get_balance.php', {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
      if (data.success && typeof data.balance !== 'undefined') {
        const el = document.getElementById('header-credit-balance');
        if (el) {
          el.textContent = data.balance;
        }
      }
    })
    .catch(err => {
      console.error('Error fetching credit balance:', err);
    });
  }

  // Run once on load
  fetchCreditBalance();

  // Then poll every 60s (adjust as you like)
  setInterval(fetchCreditBalance, 60000);

  // … (the rest of your code for notifications, “mark as read”, etc.) …
});
</script>

