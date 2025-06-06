<?php
// profile_edit.php

require 'inc/_global/config.php';
require 'inc/_global/login_check.php';   // starts the session
require 'inc/backend/config.php';

$cb->l_m_content = 'narrow';

// 4) Include page‐start scaffolding
require 'inc/_global/views/head_start.php';
require 'inc/_global/views/head_end.php';
require 'inc/_global/views/page_start.php';

// 1) Get current user ID
$currentUserId = (int)$_SESSION['user_id'];

// 2) Generate CSRF token if needed
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3) Fetch existing profile values (including followers_only)
$fetchStmt = $pdo->prepare("
    SELECT
      COALESCE(up.first_name, '')            AS first_name,
      COALESCE(up.last_name, '')             AS last_name,
      COALESCE(up.about_me, '')              AS about_me,
      COALESCE(up.date_of_birth, '')         AS date_of_birth,
      COALESCE(up.gender, '')                AS gender,
      COALESCE(up.city, '')                  AS city,
      COALESCE(up.country, '')               AS country,
      COALESCE(up.discord_username, '')      AS discord_username,
      COALESCE(up.website_url, '')           AS website_url,
      COALESCE(up.steam_profile_url, '')     AS steam_profile_url,
      COALESCE(up.github_username, '')       AS github_username,
      COALESCE(up.twitter_handle, '')        AS twitter_handle,
      COALESCE(up.profile_picture_url, '')   AS profile_picture_url,
      COALESCE(up.cover_photo_url, '')       AS cover_photo_url,
      COALESCE(up.is_public, 1)              AS is_public,
      COALESCE(up.followers_only, 0)         AS followers_only
    FROM `demon_users` AS u
    LEFT JOIN `demon_user_profiles` AS up ON up.user_id = u.id
    WHERE u.id = :uid
    LIMIT 1
");
$fetchStmt->execute(['uid' => $currentUserId]);
$profile = $fetchStmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="content">
  <!-- Placeholder for AJAX alerts -->
  <div id="ajax-alert-container"></div>

  <div class="row">
    <!-- LEFT SIDEBAR (tab navigation) -->
    <div class="col-md-3">
      <div class="block block-rounded block-bordered">
        <div class="block-header">
          <h3 class="block-title">Edit Profile</h3>
        </div>
        <div class="list-group list-group-flush">

          <a href="#tab-general" 
             class="list-group-item list-group-item-action active" 
             data-bs-toggle="list">
            <i class="fa-solid fa-user me-2"></i> General
          </a>

          <a href="#tab-social" 
             class="list-group-item list-group-item-action" 
             data-bs-toggle="list">
            <i class="fa-solid fa-share-nodes me-2"></i> Social
          </a>

          <a href="#tab-photos" 
             class="list-group-item list-group-item-action" 
             data-bs-toggle="list">
            <i class="fa-solid fa-image me-2"></i> Photos
          </a>

          <a href="#tab-privacy" 
             class="list-group-item list-group-item-action" 
             data-bs-toggle="list">
            <i class="fa-solid fa-lock me-2"></i> Privacy
          </a>

        </div>
      </div>
    </div>
    <!-- END LEFT SIDEBAR -->

    <!-- RIGHT CONTENT AREA (tab panes) -->
    <div class="col-md-9">
      <div class="block block-rounded block-bordered">
        <div class="block-content block-content-full">
          <form id="profile-edit-form"
                action="/inc/_global/save_profile.php"
                method="POST"
                enctype="multipart/form-data">

            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

            <div class="row">
              <div class="col-12">
                <div class="tab-content">

                  <!-- General Tab Pane -->
                  <div class="tab-pane fade show active" id="tab-general">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="first_name">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                               value="<?= htmlspecialchars($profile['first_name']); ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="last_name">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                               value="<?= htmlspecialchars($profile['last_name']); ?>">
                      </div>
                      <div class="col-12 mb-3">
                        <label class="form-label" for="about_me">About Me</label>
                        <textarea class="form-control" id="about_me" name="about_me" rows="4"><?= htmlspecialchars($profile['about_me']); ?></textarea>
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="date_of_birth">Birthdate</label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                               value="<?= htmlspecialchars($profile['date_of_birth']); ?>">
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="gender">Gender</label>
                        <select class="form-select" id="gender" name="gender">
                          <option value="" <?= $profile['gender'] === '' ? 'selected' : ''; ?>>Prefer not to say</option>
                          <option value="Male" <?= $profile['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                          <option value="Female" <?= $profile['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                          <option value="Other" <?= $profile['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="city">City</label>
                        <input type="text" class="form-control" id="city" name="city"
                               value="<?= htmlspecialchars($profile['city']); ?>">
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="country">Country</label>
                        <input type="text" class="form-control" id="country" name="country"
                               value="<?= htmlspecialchars($profile['country']); ?>">
                      </div>
                    </div>
                  </div>
                  <!-- END General Tab Pane -->

                  <!-- Social Tab Pane -->
                  <div class="tab-pane fade" id="tab-social">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="discord_username">Discord Username</label>
                        <input type="text" class="form-control" id="discord_username" name="discord_username"
                               value="<?= htmlspecialchars($profile['discord_username']); ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="website_url">Website URL</label>
                        <input type="url" class="form-control" id="website_url" name="website_url"
                               value="<?= htmlspecialchars($profile['website_url']); ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="steam_profile_url">Steam Profile URL</label>
                        <input type="url" class="form-control" id="steam_profile_url" name="steam_profile_url"
                               value="<?= htmlspecialchars($profile['steam_profile_url']); ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="github_username">GitHub Username</label>
                        <input type="text" class="form-control" id="github_username" name="github_username"
                               value="<?= htmlspecialchars($profile['github_username']); ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="twitter_handle">Twitter Handle</label>
                        <input type="text" class="form-control" id="twitter_handle" name="twitter_handle"
                               value="<?= htmlspecialchars($profile['twitter_handle']); ?>">
                      </div>
                    </div>
                  </div>
                  <!-- END Social Tab Pane -->

                  <!-- Photos Tab Pane -->
                  <div class="tab-pane fade" id="tab-photos">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Avatar</label>
                        <div class="mb-2">
                          <input type="text" class="form-control" id="profile_picture_url" name="profile_picture_url"
                                 placeholder="Existing or new image URL"
                                 value="<?= htmlspecialchars($profile['profile_picture_url']); ?>">
                        </div>
                        <div>
                          <input type="file" class="form-control" id="avatar_file" name="avatar_file" accept="image/*">
                          <div class="form-text">Upload a new avatar (JPEG, PNG, GIF, or WEBP)</div>
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Cover Photo</label>
                        <div class="mb-2">
                          <input type="text" class="form-control" id="cover_photo_url" name="cover_photo_url"
                                 placeholder="Existing or new cover URL"
                                 value="<?= htmlspecialchars($profile['cover_photo_url']); ?>">
                        </div>
                        <div>
                          <input type="file" class="form-control" id="cover_file" name="cover_file" accept="image/*">
                          <div class="form-text">Upload a new cover (JPEG, PNG, GIF, or WEBP)</div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- END Photos Tab Pane -->

                  <!-- Privacy Tab Pane -->
                  <div class="tab-pane fade" id="tab-privacy">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                          <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1"
                                 <?= $profile['is_public'] ? 'checked' : ''; ?>>
                          <label class="form-check-label" for="is_public">
                            Make my profile public
                          </label>
                        </div>
                      </div>
                      <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                          <input class="form-check-input" type="checkbox" id="followers_only" name="followers_only" value="1"
                                 <?= $profile['followers_only'] ? 'checked' : ''; ?>>
                          <label class="form-check-label" for="followers_only">
                            Only allow followers to view my profile
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- END Privacy Tab Pane -->

                </div>
              </div>
            </div>

            <div class="block-content block-content-full block-content-sm bg-body-light text-end">
              <button type="submit" class="btn btn-alt-primary">
                <i class="fa fa-save me-1"></i> Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- END RIGHT CONTENT AREA -->
  </div>
</div>

<?php
require 'inc/_global/views/page_end.php';
require 'inc/_global/views/footer_start.php';
?>

<!-- AJAX submission script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('profile-edit-form');
  const alertContainer = document.getElementById('ajax-alert-container');

  if (!form) {
    console.error('profile-edit-form not found');
    return;
  }

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    alertContainer.innerHTML = ''; // Clear prior alerts

    const formData = new FormData(form);
    fetch('/inc/_global/save_profile.php', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      const alertDiv = document.createElement('div');
      alertDiv.classList.add('alert', data.success ? 'alert-success' : 'alert-danger');
      alertDiv.textContent = data.message;
      alertContainer.appendChild(alertDiv);
      setTimeout(() => alertDiv.remove(), 5000);
    })
    .catch(err => {
      console.error(err);
      const alertDiv = document.createElement('div');
      alertDiv.classList.add('alert', 'alert-danger');
      alertDiv.textContent = 'An error occurred. Please try again.';
      alertContainer.appendChild(alertDiv);
      setTimeout(() => alertDiv.remove(), 5000);
    });
  });

  // Activate the first tab on page load
  const firstTabEl = document.querySelector('#tab-general');
  if (firstTabEl) {
    const tabTrigger = new bootstrap.Tab(document.querySelector('a[href="#tab-general"]'));
    tabTrigger.show();
  }
});
</script>

<?php
require 'inc/_global/views/footer_end.php';
?>
