<?php
// profile.php

require 'inc/_global/config.php';
require 'inc/_global/login_check.php';   // starts the session
require 'inc/backend/config.php';

$cb->l_m_content = 'narrow';

// Template setup: head and page‐wrapper must come before any profile logic
require 'inc/_global/views/head_start.php';
require 'inc/_global/views/head_end.php';
require 'inc/_global/views/page_start.php';

/**
 *  1) Get current user ID from session
 */
$currentUserId = (int)$_SESSION['user_id'];

/**
 *  2) Determine whose profile to view (default = current user)
 */
$viewUserId = isset($_GET['uid']) ? (int)$_GET['uid'] : $currentUserId;
$canEdit    = ($viewUserId === $currentUserId);

/**
 *  3) One‐off CSRF token (for AJAX follow/unfollow & posts & reacts)
 */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 *  4) Fetch user + role + profile_info (including is_public & followers_only)
 */
$stmt = $pdo->prepare("
    SELECT
      u.username,
      u.email,
      u.created_at                  AS joined,
      COALESCE(r.name, '')          AS role_name,
      COALESCE(up.first_name, '')   AS first_name,
      COALESCE(up.last_name, '')    AS last_name,
      COALESCE(up.about_me, '')     AS about_me,
      COALESCE(up.date_of_birth, '') AS date_of_birth,
      COALESCE(up.gender, '')       AS gender,
      COALESCE(up.city, '')         AS city,
      COALESCE(up.country, '')      AS country,
      COALESCE(up.discord_username, '')   AS discord_username,
      COALESCE(up.website_url, '')         AS website_url,
      COALESCE(up.steam_profile_url, '')   AS steam_profile_url,
      COALESCE(up.github_username, '')     AS github_username,
      COALESCE(up.twitter_handle, '')      AS twitter_handle,
      COALESCE(up.profile_picture_url, u.profile_picture_url, '') AS avatar,
      COALESCE(up.cover_photo_url, '')     AS cover_photo_url,
      COALESCE(up.is_public, 1)            AS is_public,
      COALESCE(up.followers_only, 0)       AS followers_only
    FROM `demon_users` AS u
    LEFT JOIN `demon_user_profiles` AS up ON up.user_id = u.id
    LEFT JOIN `demon_roles`        AS r  ON r.id      = u.role_id
    WHERE u.id = :uid
    LIMIT 1
");
$stmt->execute(['uid' => $viewUserId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    echo "User not found.";
    exit;
}

/**
 *  5) Check if current user follows this profile (if not own)
 */
$isFollowing = false;
if (!$canEdit) {
    $chkFollow = $pdo->prepare("
      SELECT 1 FROM `demon_follows`
      WHERE user_id = :view AND follower_id = :cur
      LIMIT 1
    ");
    $chkFollow->execute([
      'view'=> $viewUserId,
      'cur' => $currentUserId
    ]);
    $isFollowing = (bool)$chkFollow->fetchColumn();
}

/**
 *  6) Privacy checks:
 *     a) If profile is private (is_public = 0) and not the owner, show a warning & exit
 *     b) If followers_only = 1 and viewer is neither owner nor already following, show “followers only” & exit
 */
if ((int)$profile['is_public'] === 0 && !$canEdit) {
    ?>
    <div class="content">
      <div class="alert alert-warning text-center">
        This user’s profile is private.
      </div>
    </div>
    <?php
    require 'inc/_global/views/page_end.php';
    require 'inc/_global/views/footer_start.php';
    require 'inc/_global/views/footer_end.php';
    exit;
}

if ((int)$profile['followers_only'] === 1 && !$canEdit && !$isFollowing) {
    ?>
    <div class="content">
      <div class="alert alert-info text-center">
        This user’s profile is only visible to their followers.
      </div>
    </div>
    <?php
    require 'inc/_global/views/page_end.php';
    require 'inc/_global/views/footer_start.php';
    require 'inc/_global/views/footer_end.php';
    exit;
}

/**
 *  7) Compute avatar & cover URLs (with fallbacks)
 */
$avatarUrl = trim($profile['avatar']) !== ''
    ? $profile['avatar']
    : ($cb->assets_folder . '/media/avatars/avatar_blank.png');

$coverUrl = trim($profile['cover_photo_url']) !== ''
    ? $profile['cover_photo_url']
    : ($cb->assets_folder . '/media/photos/photo13@2x.jpg');

/**
 *  8) Fetch follower/following counts
 */
$countFollowers = $pdo->prepare("SELECT COUNT(*) FROM `demon_follows` WHERE user_id = :uid");
$countFollowers->execute(['uid' => $viewUserId]);
$followersCount = (int)$countFollowers->fetchColumn();

$countFollowing = $pdo->prepare("SELECT COUNT(*) FROM `demon_follows` WHERE follower_id = :uid");
$countFollowing->execute(['uid' => $viewUserId]);
$followingCount = (int)$countFollowing->fetchColumn();

// Fetch the list of users who follow $viewUserId
$followersStmt = $pdo->prepare("
  SELECT
    f.follower_id AS id,
    u.username,
    COALESCE(up.profile_picture_url, u.profile_picture_url, '') AS avatar_url,
    COALESCE(r.name, '') AS role_name
  FROM `demon_follows` AS f
  JOIN `demon_users` AS u ON u.id = f.follower_id
  LEFT JOIN `demon_user_profiles` AS up ON up.user_id = u.id
  LEFT JOIN `demon_roles` AS r ON r.id = u.role_id
  WHERE f.user_id = :uid
  ORDER BY u.username
");
$followersStmt->execute(['uid' => $viewUserId]);
$followersList = $followersStmt->fetchAll(PDO::FETCH_ASSOC);

/**
 *  9) Fetch recent audit activity (last 5 entries)
 */
$activityStmt = $pdo->prepare("
  SELECT action, details, ip_address, created_at
  FROM `demon_audit_logs`
  WHERE user_id = :uid
  ORDER BY created_at DESC
  LIMIT 5
");
$activityStmt->execute(['uid' => $viewUserId]);
$recentActivity = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * 10) Load flash messages
 */
$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError   = $_SESSION['flash_error']   ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

/**
 * 11) Fetch all profile‐posts for $viewUserId, including poster name fields
 */
$postStmt = $pdo->prepare("
  SELECT
    p.id,
    p.user_id,
    p.title,
    p.message,
    p.created_at,
    u.username,
    COALESCE(up.first_name, '')  AS first_name,
    COALESCE(up.last_name, '')   AS last_name
  FROM `demon_user_profile_posts` AS p
  JOIN `demon_users` AS u ON u.id = p.user_id
  LEFT JOIN `demon_user_profiles` AS up ON up.user_id = u.id
  WHERE p.user_id = :uid
  ORDER BY p.created_at DESC
");
$postStmt->execute(['uid' => $viewUserId]);
$posts = $postStmt->fetchAll(PDO::FETCH_ASSOC);

// Build array of post IDs for a single reaction‐count query
$postIds = array_column($posts, 'id');
$reactionCounts = [];
if (!empty($postIds)) {
    $inClause = implode(',', array_fill(0, count($postIds), '?'));
    $countsStmt = $pdo->prepare("
      SELECT post_id, react_type, COUNT(*) AS cnt
      FROM `demon_user_profile_reacts`
      WHERE post_id IN ($inClause)
      GROUP BY post_id, react_type
    ");
    $countsStmt->execute($postIds);

    // Initialize all counts to zero
    foreach ($postIds as $pid) {
        $reactionCounts[$pid] = [
            'like'  => 0,
            'love'  => 0,
            'laugh' => 0,
            'angry' => 0
        ];
    }
    // Fill in actual counts based on query result
    while ($rowC = $countsStmt->fetch(PDO::FETCH_ASSOC)) {
        $pid  = (int)$rowC['post_id'];
        $type = $rowC['react_type'];
        $reactionCounts[$pid][$type] = (int)$rowC['cnt'];
    }
}
?>

<!-- Flash Messages -->
<div class="content mb-3">
  <?php if ($flashSuccess): ?>
    <div class="alert alert-success"><?= htmlspecialchars($flashSuccess); ?></div>
  <?php endif; ?>
  <?php if ($flashError): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($flashError); ?></div>
  <?php endif; ?>
</div>

<!-- Page Content -->
<div class="content">
  <div class="row">
    <!-- Left Column: User Account -->
    <div class="col-lg-4 col-xl-3">
      <!-- Account -->
      <div class="block block-rounded text-center fw-semibold">
        <?php if (trim($profile['cover_photo_url'] ?? '') !== ''): ?>
          <div
            class="block-content block-content-full"
            style="background-image: url('<?= htmlspecialchars($coverUrl); ?>'); background-size: cover; background-position: center;"
          >
        <?php else: ?>
          <div class="block-content block-content-full bg-gd-sea">
        <?php endif; ?>
            <a class="img-link" href="profile.php?uid=<?= $viewUserId; ?>">
              <img
                class="img-avatar img-avatar96 rounded-circle"
                src="<?= htmlspecialchars($avatarUrl); ?>"
                alt="Avatar"
              >
            </a>
          </div>
        <div class="block-content p-3">
          <div class="border-bottom pb-3 mb-3">
            <?php
              $fn = trim($profile['first_name'] ?? '');
              $ln = trim($profile['last_name'] ?? '');
              $displayName = ($fn !== '' || $ln !== '') ? "$fn $ln" : ($profile['username'] ?? '');
              echo htmlspecialchars($displayName);
            ?>
            <?php if (!$canEdit): ?>
              <!-- Follow/Unfollow Button -->
              <?php if ($isFollowing): ?>
                <button
                  id="follow-btn"
                  data-uid="<?= $viewUserId; ?>"
                  data-csrf="<?= $_SESSION['csrf_token']; ?>"
                  class="btn btn-sm btn-outline-danger ms-2"
                >Unfollow</button>
              <?php else: ?>
                <button
                  id="follow-btn"
                  data-uid="<?= $viewUserId; ?>"
                  data-csrf="<?= $_SESSION['csrf_token']; ?>"
                  class="btn btn-sm btn-outline-primary ms-2"
                >Follow</button>
              <?php endif; ?>
            <?php endif; ?>
            <br>
            <a class="fs-sm fw-medium text-muted" href="profile.php?uid=<?= $viewUserId; ?>">
              @<?= htmlspecialchars($profile['username'] ?? ''); ?>
            </a>
          </div>
          <div class="row g-sm">
            <div class="col-4">
              <div class="fs-xs text-muted">Posts</div>
              <a id="post-count" class="fs-lg" href="javascript:void(0);">
                <?= count($posts); ?>
              </a>
            </div>
            <div class="col-4">
              <div class="fs-xs text-muted">Following</div>
              <a id="followers-count" class="fs-lg" href="javascript:void(0);">
                <?= $followingCount; ?>
              </a>
            </div>
            <div class="col-4">
              <div class="fs-xs text-muted">Followers</div>
              <a id="followers-count" class="fs-lg" href="javascript:void(0);">
                <?= $followersCount; ?>
              </a>
            </div>
          </div>

          <!-- Separator & Edit Profile Button (only for owner) -->
          <?php if ($canEdit): ?>
            <div class="border-top mt-3 pt-2">
              <a href="profile_edit.php" class="btn btn-sm btn-alt-secondary w-100">
                <i class="fa fa-edit me-1"></i> Edit Profile
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <!-- END Account -->





      <!-- Profile Info (formerly Worldwide Trends) -->
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title fw-semibold">Profile Info</h3>
          <div class="block-options">
            <button type="button" class="btn-block-option" data-toggle="block-option" data-action="state_toggle" data-action-mode="demo">
              <i class="si si-refresh"></i>
            </button>
            <button type="button" class="btn-block-option">
              <i class="si si-wrench"></i>
            </button>
          </div>
        </div>
        <div class="block-content p-3 text-muted fs-sm">
          <!-- About Me -->
          <?php if (!empty($profile['about_me'])): ?>
            <p><strong>About:</strong><br><?= nl2br(htmlspecialchars($profile['about_me'])); ?></p>
          <?php endif; ?>

          <!-- Email -->
          <?php if (!empty($profile['email'])): ?>
            <p><strong>Email:</strong> <?= htmlspecialchars($profile['email']); ?></p>
          <?php endif; ?>

          <!-- Birthdate -->
          <?php if (!empty($profile['date_of_birth'])): ?>
            <p><strong>Birthdate:</strong> <?= date('M j, Y', strtotime($profile['date_of_birth'])); ?></p>
          <?php endif; ?>

          <!-- Location -->
          <?php
            $city    = trim($profile['city'] ?? '');
            $country = trim($profile['country'] ?? '');
            if ($city !== '' || $country !== ''):
          ?>
            <p><strong>Location:</strong> <?= htmlspecialchars($city . ($city && $country ? ', ' : '') . $country); ?></p>
          <?php endif; ?>

          <!-- Discord -->
          <?php if (!empty($profile['discord_username'])): ?>
            <p><strong>Discord:</strong> <?= htmlspecialchars($profile['discord_username']); ?></p>
          <?php endif; ?>

          <!-- Website -->
          <?php if (!empty($profile['website_url'])): ?>
            <p><strong>Website:</strong>
              <a href="<?= htmlspecialchars($profile['website_url']); ?>" target="_blank">
                <?= htmlspecialchars($profile['website_url']); ?>
              </a>
            </p>
          <?php endif; ?>

          <!-- GitHub -->
          <?php if (!empty($profile['github_username'])): ?>
            <p><strong>GitHub:</strong>
              <a href="https://github.com/<?= htmlspecialchars($profile['github_username']); ?>" target="_blank">
                <?= htmlspecialchars($profile['github_username']); ?>
              </a>
            </p>
          <?php endif; ?>

          <!-- Twitter -->
          <?php if (!empty($profile['twitter_handle'])): ?>
            <p><strong>Twitter:</strong>
              <a href="https://twitter.com/<?= ltrim(htmlspecialchars($profile['twitter_handle']), '@'); ?>" target="_blank">
                <?= htmlspecialchars($profile['twitter_handle']); ?>
              </a>
            </p>
          <?php endif; ?>

          <!-- Steam -->
          <?php if (!empty($profile['steam_profile_url'])): ?>
            <p><strong>Steam:</strong>
              <a href="<?= htmlspecialchars($profile['steam_profile_url']); ?>" target="_blank">
                <?= htmlspecialchars($profile['steam_profile_url']); ?>
              </a>
            </p>
          <?php endif; ?>

          <!-- Role -->
          <?php if (!empty($profile['role_name'])): ?>
            <p><strong>Role:</strong> <?= htmlspecialchars($profile['role_name']); ?></p>
          <?php endif; ?>

          <!-- Joined Date -->
          <p><strong>Joined:</strong>
            <?= !empty($profile['joined'])
                 ? date('M j, Y', strtotime($profile['joined']))
                 : 'N/A'; ?>
          </p>
        </div>
      </div>
      <!-- END Profile Info -->
    </div>
    <!-- END Left Column -->


    <!-- Middle Column: Updates -->
    <div class="col-lg-4 col-xl-6">
      <div class="block block-rounded">
        <!-- New Post Toggle & Form -->
        <?php if ($canEdit): ?>
          <div class="block-content block-content-full bg-body-light">
            <button class="btn btn-alt-primary w-100" data-bs-toggle="collapse" data-bs-target="#new-post-collapse">
              <i class="fa fa-plus-circle me-1"></i> What's on your mind?
            </button>
          </div>
          <div id="new-post-collapse" class="collapse">
            <div class="block-content">
              <form id="new-post-form" method="POST" action="/inc/_global/ajax_profile_post.php">
                <input type="hidden" name="user_id" value="<?= $currentUserId; ?>">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

                <div class="mb-3">
                  <label class="form-label" for="post-title">Title</label>
                  <input type="text" class="form-control" id="post-title" name="title" required>
                </div>
                <div class="mb-3">
                  <label class="form-label" for="post-message">Message</label>
                  <textarea class="form-control" id="post-message" name="message" rows="3" required></textarea>
                </div>
                <div class="text-end">
                  <button type="submit" class="btn btn-alt-success">
                    <i class="fa fa-paper-plane me-1"></i> Post
                  </button>
                </div>
              </form>
            </div>
          </div>
        <?php endif; ?>

        <!-- Existing Posts -->
        <?php if (empty($posts)): ?>
          <div class="block-content block-content-full">
            <p class="text-muted">No posts yet.</p>
          </div>
        <?php endif; ?>

        <?php foreach ($posts as $row):
            $pid = (int)$row['id'];
            $posterId = (int)$row['user_id'];
            $counts = $reactionCounts[$pid] ?? ['like' => 0, 'love' => 0, 'laugh' => 0, 'angry' => 0];

            // Convert UTC created_at → Europe/London ISO-8601
            $dt = new DateTime($row['created_at'], new DateTimeZone('UTC'));
            $dt->setTimezone(new DateTimeZone('Europe/London'));
            $ukISO = $dt->format('Y-m-d\TH:i:s');
        ?>
          <div id="post-<?= $pid; ?>" class="block-content block-content-full border-top">
            <div class="d-flex">
              <!-- Poster Avatar -->
              <div class="flex-shrink-0 me-3">
                <?php
                  $posterAvatar = ($posterId === $viewUserId) ? $avatarUrl : get_user_avatar_url($posterId);
                ?>
                <img class="img-avatar img-avatar48 rounded-circle" src="<?= htmlspecialchars($posterAvatar); ?>" alt="Avatar">
              </div>
              <div class="flex-grow-1">
                <?php
                  // Determine display name
                  $posterFn    = trim($row['first_name']  ?? '');
                  $posterLn    = trim($row['last_name']   ?? '');
                  $posterUsername = htmlspecialchars($row['username'] ?? '');
                  if ($posterFn !== '' || $posterLn !== '') {
                    $posterFull = htmlspecialchars("$posterFn $posterLn");
                    echo '<p class="mb-1">'
                         . '<a class="fw-semibold" href="profile.php?uid=' . $posterId . '">' . $posterFull . '</a> '
                         . '<a class="fs-sm fw-medium text-muted" href="profile.php?uid=' . $posterId . '">@' . $posterUsername . '</a> '
                         . '&bull; '
                         . '<span class="text-muted"><time class="time-ago" data-ts="' . htmlspecialchars($ukISO) . '"></time></span>'
                         . '</p>';
                  } else {
                    echo '<p class="mb-1">'
                         . '<a class="fw-semibold" href="profile.php?uid=' . $posterId . '">@' . $posterUsername . '</a> '
                         . '&bull; '
                         . '<span class="text-muted"><time class="time-ago" data-ts="' . htmlspecialchars($ukISO) . '"></time></span>'
                         . '</p>';
                  }
                ?>

                <!-- Post Title -->
                <?php if (!empty($row['title'])): ?>
                  <h5 class="post-title mb-1"><?= htmlspecialchars($row['title']); ?></h5>
                <?php endif; ?>

                <!-- Post Message -->
                <p class="fs-sm mb-2"><?= nl2br(htmlspecialchars($row['message'])); ?></p>

                <!-- Reactions (if viewer is not the poster) -->
                <?php if ($posterId !== $currentUserId): ?>
                  <div class="mb-2">
                    <?php foreach (['like' => '👍', 'love' => '❤️', 'laugh' => '😂', 'angry' => '😡'] as $type => $emoji): ?>
                      <button type="button"
                              title="<?= ucfirst($type); ?>"
                              class="btn btn-sm rounded-pill btn-alt-secondary me-2 react-btn"
                              data-post-id="<?= $pid; ?>"
                              data-react="<?= $type; ?>">
                        <?= $emoji; ?>
                        <span class="react-count" data-react="<?= $type; ?>"><?= $counts[$type]; ?></span>
                      </button>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <!-- Edit/Delete if the post belongs to current user -->
                  <div class="text-end mb-2">
                    <button class="btn btn-sm btn-link edit-post-btn" data-post-id="<?= $pid; ?>">
                      <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-link delete-post-btn text-danger" data-post-id="<?= $pid; ?>">
                      <i class="fa fa-trash-alt"></i>
                    </button>
                  </div>
                  <!-- Edit Form (collapsed) -->
                  <div id="edit-form-<?= $pid; ?>" class="collapse mb-3">
                    <form class="edit-post-form" method="POST" action="/inc/_global/ajax_profile_post.php">
                      <input type="hidden" name="post_id" value="<?= $pid; ?>">
                      <input type="hidden" name="action" value="update">
                      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

                      <div class="mb-2">
                        <label class="form-label" for="edit-title-<?= $pid; ?>">Title</label>
                        <input type="text"
                               class="form-control"
                               id="edit-title-<?= $pid; ?>"
                               name="title"
                               value="<?= htmlspecialchars($row['title']); ?>"
                               required>
                      </div>
                      <div class="mb-2">
                        <label class="form-label" for="edit-message-<?= $pid; ?>">Message</label>
                        <textarea class="form-control"
                                  id="edit-message-<?= $pid; ?>"
                                  name="message"
                                  rows="3"
                                  required><?= htmlspecialchars($row['message']); ?></textarea>
                      </div>
                      <div class="text-end">
                        <button type="submit" class="btn btn-sm btn-alt-success">
                          <i class="fa fa-save me-1"></i> Save Changes
                        </button>
                      </div>
                    </form>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <!-- END Middle Column -->


    <!-- Right Column: Followers & Footer -->
    <div class="col-lg-4 col-xl-3">
      <!-- Followers (formerly “Who to follow”) -->
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title fw-semibold">Following Me</h3>
          <div class="block-options">
            <button type="button" class="btn-block-option" data-toggle="block-option" data-action="state_toggle" data-action-mode="demo">
              <i class="si si-refresh"></i>
            </button>
            <button type="button" class="btn-block-option">
              <i class="si si-wrench"></i>
            </button>
          </div>
        </div>
        <div class="block-content p-2">
          <ul id="followers-list" class="nav-users">
            <?php if (isset($followersList) && count($followersList) > 0): ?>
              <?php foreach ($followersList as $f): ?>
                <li class="fs-sm mb-2">
                  <a href="profile.php?uid=<?= (int)$f['id']; ?>" class="d-flex align-items-center">
                    <img
                      class="img-avatar img-avatar32 rounded-circle me-2"
                      src="<?= htmlspecialchars($f['avatar_url']); ?>"
                      alt="Avatar">
                    <div class="flex-grow-1">
                      <?= htmlspecialchars($f['username']); ?><br>
                      <span class="fw-medium text-muted"><?= htmlspecialchars($f['role_name'] ?? ''); ?></span>
                    </div>
                  </a>
                </li>
              <?php endforeach; ?>
            <?php else: ?>
              <li class="fs-sm text-muted">No followers yet.</li>
            <?php endif; ?>
          </ul>
        </div>
        <div class="block-content p-3 border-top text-center">
          <a class="fs-sm fw-medium" href="javascript:void(0);">
            <i class="fa fa-users opacity-50 me-1"></i> View All Followers
          </a>
        </div>
      </div>
      <!-- END Followers -->

      <!-- About & Footer Links -->
      <div class="block block-rounded">
        <div class="block-content p-3 text-muted fs-sm">
          &copy; <span data-toggle="year-copy"></span>
          <a class="fs-sm fw-medium text-muted ms-1" href="javascript:void(0)">About Us</a>
          <a class="fs-sm fw-medium text-muted ms-1" href="javascript:void(0)">Copyright</a>
        </div>
        <div class="block-content p-3 border-top">
          <a class="fs-sm fw-medium" href="javascript:void(0)">
            <i class="fa fa-external-link-square-alt opacity-50 me-1"></i> Advertise with Us
          </a>
        </div>
      </div>
      <!-- END About & Footer Links -->
    </div>
    <!-- END Right Column -->
  </div>
</div>
<!-- END Page Content -->

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="confirmDeleteModalLabel" class="modal-title">Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this post?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirm-delete-btn" class="btn btn-danger btn-sm">Yes, Delete</button>
      </div>
    </div>
  </div>
</div>

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- AJAX: Follow/Unfollow -->
<!-- AJAX: Follow/Unfollow + Live‐Update Followers List -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const btn = document.getElementById('follow-btn');
  if (!btn) return;

  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const uid = btn.getAttribute('data-uid');
    const csrfToken = btn.getAttribute('data-csrf');
    const action = btn.textContent.trim().toLowerCase() === 'follow' ? 'follow' : 'unfollow';

    fetch('/inc/_global/ajax_follow.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ uid, action, csrf_token: csrfToken })
    })
    .then(res => res.json())
    .then(data => {
      if (!data.success) {
        alert(data.error || 'Unexpected error.');
        return;
      }

      // 1) Update the Follow/Unfollow button text + classes
      if (data.isFollowing) {
        btn.textContent = 'Unfollow';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-outline-danger');
      } else {
        btn.textContent = 'Follow';
        btn.classList.remove('btn-outline-danger');
        btn.classList.add('btn-outline-primary');
      }

      // 2) Update the follower count number
      const countEl = document.getElementById('followers-count');
      if (countEl) {
        countEl.textContent = data.followersCount;
      }

      // 3) Fetch and re-render the "Following Me" list
      fetch('/inc/_global/ajax_followers.php?uid=' + uid)
        .then(r => r.json())
        .then(followers => {
          const ul = document.getElementById('followers-list');
          if (!ul) return;

          // If no followers, show the fallback message
          if (!Array.isArray(followers) || followers.length === 0) {
            ul.innerHTML = '<li class="fs-sm text-muted">No followers yet.</li>';
            return;
          }

          // Otherwise, rebuild the <li> items
          ul.innerHTML = '';
          followers.forEach(f => {
            const li = document.createElement('li');
            li.className = 'fs-sm mb-2';
            li.innerHTML = `
              <a href="profile.php?uid=${f.id}" class="d-flex align-items-center">
                <img
                  class="img-avatar img-avatar32 rounded-circle me-2"
                  src="${f.avatar_url || ''}"
                  alt="Avatar">
                <div class="flex-grow-1">
                  ${f.username}
                  <br>
                  <span class="fw-medium text-muted">${f.role_name || ''}</span>
                </div>
              </a>`;
            ul.appendChild(li);
          });
        })
        .catch(err => {
          console.error('Failed to load updated followers list:', err);
        });
    })
    .catch(err => {
      console.error(err);
      alert('An error occurred. Please try again.');
    });
  });
});
</script>


<!-- AJAX: New/Edit/Delete Profile Post -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const newForm = document.getElementById('new-post-form');
  if (newForm) {
    newForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(newForm);

      fetch('/inc/_global/ajax_profile_post.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          alert(data.error || 'Failed to create post.');
          return;
        }
        // On success, reload page to see the new post
        location.reload();
      })
      .catch(err => {
        console.error(err);
        alert('An error occurred. Please try again.');
      });
    });
  }

  // Edit post buttons
  document.querySelectorAll('.edit-post-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const pid = this.getAttribute('data-post-id');
      const collapseEl = document.getElementById('edit-form-' + pid);
      if (collapseEl) {
        // Toggle the collapse for this post's edit form
        new bootstrap.Collapse(collapseEl, { toggle: true });
      }
    });
  });

  // Handle edit form submissions via AJAX
  document.querySelectorAll('.edit-post-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      fetch('/inc/_global/ajax_profile_post.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          alert(data.error || 'Failed to update post.');
          return;
        }
        // On success, reload to see updated message
        location.reload();
      })
      .catch(err => {
        console.error(err);
        alert('An error occurred. Please try again.');
      });
    });
  });

  // Delete post buttons (use Bootstrap modal for confirmation)
  let postIdToDelete = null;

  document.querySelectorAll('.delete-post-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      postIdToDelete = this.getAttribute('data-post-id');
      const modalEl = document.getElementById('confirmDeleteModal');
      const bsModal = new bootstrap.Modal(modalEl);
      bsModal.show();
    });
  });

  document.getElementById('confirm-delete-btn').addEventListener('click', function() {
    if (!postIdToDelete) return;
    const csrfToken = '<?= $_SESSION['csrf_token']; ?>';

    fetch('/inc/_global/ajax_profile_post.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        post_id: postIdToDelete,
        action: 'delete',
        csrf_token: csrfToken
      })
    })
    .then(res => res.json())
    .then(data => {
      if (!data.success) {
        alert(data.error || 'Failed to delete post.');
        return;
      }
      // On success, remove the post from the DOM
      const postDiv = document.getElementById('post-' + postIdToDelete);
      if (postDiv) postDiv.remove();

      // ↓ Decrement the Posts count in the Account block
      const countEl = document.getElementById('post-count');
      if (countEl) {
        let current = parseInt(countEl.textContent, 10) || 0;
        current = Math.max(0, current - 1);
        countEl.textContent = current;
      }

      // Hide the modal
      const modalEl = document.getElementById('confirmDeleteModal');
      const bsModal = bootstrap.Modal.getInstance(modalEl);
      bsModal.hide();
    })
    .catch(err => {
      console.error(err);
      alert('An error occurred. Please try again.');
    });
  });
});
</script>


<!-- AJAX: React to Profile Post -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const csrfToken = '<?= $_SESSION['csrf_token']; ?>';

  document.querySelectorAll('.react-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const postId    = this.dataset.postId;
      const reactType = this.dataset.react;

      fetch('/inc/_global/ajax_profile_react.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          post_id:    postId,
          react:      reactType,
          csrf_token: csrfToken
        })
      })
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          alert(data.error || 'Unexpected error.');
          return;
        }
        // Update on‐screen counts for this post
        const postBlock = document.getElementById('post-' + postId);
        if (!postBlock) return;

        ['like','love','laugh','angry'].forEach(type => {
          const span = postBlock.querySelector('.react-count[data-react="' + type + '"]');
          if (span && data.counts[type] != null) {
            span.textContent = data.counts[type];
          }
        });
      })
      .catch(err => {
        console.error(err);
        alert('An error occurred. Please try again.');
      });
    });
  });
});
</script>

<!-- LIVE Relative Timestamps (UK time) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  function updateTimeAgo() {
    document.querySelectorAll('.time-ago').forEach(function(el) {
      const tsISO = el.getAttribute('data-ts');
      if (!tsISO) return;

      const dt = new Date(tsISO);
      if (isNaN(dt)) {
        el.textContent = '';
        return;
      }

      const now = new Date();
      let diff = Math.floor((now - dt) / 1000); // seconds difference

      let suffix = '';
      if (diff < 60) {
        suffix = 'just now';
      } else if (diff < 3600) {
        const m = Math.floor(diff / 60);
        suffix = m + ' minute' + (m !== 1 ? 's' : '') + ' ago';
      } else if (diff < 86400) {
        const h = Math.floor(diff / 3600);
        suffix = h + ' hour' + (h !== 1 ? 's' : '') + ' ago';
      } else {
        const d = Math.floor(diff / 86400);
        suffix = d + ' day' + (d !== 1 ? 's' : '') + ' ago';
      }
      el.textContent = suffix;
    });
  }
  // Initial run & repeat every minute
  updateTimeAgo();
  setInterval(updateTimeAgo, 60000);
});
</script>

<?php require 'inc/_global/views/footer_end.php'; ?>
