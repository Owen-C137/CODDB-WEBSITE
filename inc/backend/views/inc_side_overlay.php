<?php
/**
 * backend/views/inc_side_overlay.php
 *
 * Side overlay (always visible on every page). Shows:
 *  • Site logo & name (pulled from `demon_site_settings.site_name` and `demon_site_settings.logo_url`)
 *  • If logged in: a compact “Quests & Challenges” list (titles only), 
 *    with inline “collapse” panels for details.
 */

require_once __DIR__ . '/../../../inc/_global/config.php';
require_once __DIR__ . '/../../../inc/_global/login_check.php';   // session + $_SESSION['user_id']
require_once __DIR__ . '/../../../inc/backend/config.php';

$siteName    = 'My Site';
$siteLogoURL = '/assets/media/logo/light.png';

/**
 * 1) Fetch site_name & logo_url from demon_site_settings
 *    (Assumes exactly one row; adjust LIMIT if necessary)
 */
try {
    $sStmt = $pdo->prepare("
        SELECT site_name, logo_url
        FROM demon_site_settings
        LIMIT 1
    ");
    $sStmt->execute();
    $siteRow = $sStmt->fetch(PDO::FETCH_ASSOC);
    if ($siteRow) {
        if (!empty($siteRow['site_name'])) {
            $siteName = $siteRow['site_name'];
        }
        if (!empty($siteRow['logo_url'])) {
            $siteLogoURL = $siteRow['logo_url'];
        }
    }
} catch (\Exception $e) {
    // If table/columns don't exist (or error), fall back to defaults
}

/**
 * 2) If user is logged in, award “complete_profile” and build full quest list.
 */
$allQuests = [];
if (!empty($_SESSION['user_id'])) {
    $currentUserId = (int)$_SESSION['user_id'];

    // Award “complete_profile” if eligible
    require_once __DIR__ . '/../../../inc/_global/quests/index.php';
    tryCompleteProfileQuest($pdo, $currentUserId);

    // Build full quest list (with progress flags)
    require_once __DIR__ . '/../../../inc/_global/quests/quest_progression.php';
    $allQuests = buildAllQuests($pdo, $currentUserId);
}
?>
<!-- Side Overlay -->
<aside id="side-overlay">
  <!-- Side Header -->
  <div class="content-header py-2 px-3 d-flex align-items-center">
    <!-- Site Logo -->
    <a href="<?php echo htmlspecialchars((!empty($siteRow['site_url']) ? $siteRow['site_url'] : '/')); ?>" class="me-2">
      <img src="<?= htmlspecialchars($siteLogoURL); ?>" alt="<?= htmlspecialchars($siteName); ?>" style="height: 28px;">
    </a>
    <!-- Site Name -->
    <a href="<?php echo htmlspecialchars((!empty($siteRow['site_url']) ? $siteRow['site_url'] : '/')); ?>"
       class="fw-semibold text-body-color-dark fs-sm">
      <?= htmlspecialchars($siteName); ?>
    </a>
    <!-- Close Button -->
    <button type="button"
            class="btn btn-sm btn-alt-danger ms-auto p-1"
            data-toggle="layout"
            data-action="side_overlay_close"
            title="Close">
      <i class="fa fa-fw fa-times"></i>
    </button>
  </div>
  <!-- END Side Header -->

  <!-- Side Content -->
  <div class="content-side px-2">
    <?php if (empty($_SESSION['user_id'])): ?>
      <!-- If not logged in, show “Log In / Register” buttons -->
      <div class="py-3 text-center">
        <a href="/login.php" class="btn btn-sm btn-primary w-100 mb-2">
          <i class="fa fa-sign-in-alt me-1"></i> Log In
        </a>
        <a href="/register.php" class="btn btn-sm btn-secondary w-100">
          <i class="fa fa-user-plus me-1"></i> Register
        </a>
      </div>
    <?php else: ?>
      <!-- If logged in, show “Quests & Challenges” -->
      <div class="mb-3">
        <h6 class="fw-semibold text-muted mb-2 ps-1">Quests &amp; Challenges</h6>
        <ul class="list-group list-group-flush">
          <?php if (count($allQuests) === 0): ?>
            <li class="list-group-item text-center text-muted py-2">No quests available.</li>
          <?php else: ?>
            <?php foreach ($allQuests as $questKey => $questData):
                    $isDone = (bool)$questData['is_completed'];
                    $pct    = $questData['progress_pct'];
                    // sanitize for IDs / HTML
                    $safeKey = htmlspecialchars($questKey);
            ?>
              <!-- Quest Title Row -->
              <li class="list-group-item d-flex align-items-center py-2 <?= $isDone ? 'bg-success bg-opacity-10' : '' ?>">
                <div class="me-2">
                  <?php if ($isDone): ?>
                    <i class="fa fa-check-circle text-success"></i>
                  <?php else: ?>
                    <i class="fa fa-hourglass-half text-secondary"></i>
                  <?php endif; ?>
                </div>
                <div class="flex-fill fw-medium">
                  <?= htmlspecialchars($questData['title']); ?>
                </div>
                <div class="badge <?= $isDone ? 'bg-success' : 'bg-secondary'; ?> ms-2 py-1 px-2 fs-xs">
                  <?= $isDone ? '✓' : $pct . '%'; ?>
                </div>
                <!-- Toggle “collapse” for details -->
                <button
                  class="btn btn-sm btn-outline-primary ms-2 py-0 px-1 fs-xs"
                  data-bs-toggle="collapse"
                  data-bs-target="#collapse-<?= $safeKey; ?>"
                  aria-expanded="false"
                  aria-controls="collapse-<?= $safeKey; ?>"
                  title="Details">
                  <i class="fa fa-info-circle"></i>
                </button>
              </li>
              <!-- Hidden “collapse” panel with quest details -->
              <li id="collapse-<?= $safeKey; ?>" class="list-group-item collapse">
                <div class="fs-xs">
                  <p class="mb-1"><strong>Description:</strong></p>
                  <p class="fs-xxs text-muted mb-1">
                    <?= nl2br(htmlspecialchars($questData['description'])); ?>
                  </p>
                  <p class="mb-1"><strong>Reward:</strong> <?= number_format($questData['reward_amount']); ?> credits</p>
                  <hr class="my-1">
                  <?php if ($isDone): ?>
                    <div class="mt-2 text-center">
                      <span class="badge bg-success">Completed</span>
                    </div>
                  <?php else: ?>
                    <div class="mt-2 text-center">
                      <span class="badge bg-secondary"><?= $pct; ?>%</span>
                    </div>
                  <?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>
      <!-- END Quests & Challenges -->
    <?php endif; ?>
  </div>
  <!-- END Side Content -->
</aside>
<!-- END Side Overlay -->
