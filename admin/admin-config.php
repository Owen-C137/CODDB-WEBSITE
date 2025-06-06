<?php
// admin/admin-config.php

require __DIR__ . '/../inc/_global/config.php';
require __DIR__ . '/../inc/_global/login_check.php';
require __DIR__ . '/../inc/backend/config.php';

$cb->inc_header  = __DIR__ . '/../inc/backend/views/inc_header.php';
$cb->inc_sidebar = __DIR__ . '/../inc/backend/views/inc_sidebar.php';
$cb->inc_footer  = __DIR__ . '/../inc/backend/views/inc_footer.php';

// Page‐specific settings
$cb->l_m_content = 'narrow';

// Fetch available roles for the “default_user_role” dropdown
$rolesStmt = $pdo->query("SELECT `name` FROM `demon_roles` ORDER BY `level` ASC");
$allRoles = $rolesStmt->fetchAll(PDO::FETCH_COLUMN);

$successMessage = '';
$errors = [];

// Handle form submission (only when Save Changes is clicked)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {
    // Collect and sanitize all inputs (same as before)
    $in = [];
    $in['site_name']                  = trim($_POST['site_name']                  ?? '');
    $in['site_tagline']               = trim($_POST['site_tagline']               ?? '');
    $in['site_description']           = trim($_POST['site_description']           ?? '');
    $in['site_author']                = trim($_POST['site_author']                ?? '');
    $in['site_robots']                = trim($_POST['site_robots']                ?? '');
    $in['mail_driver']                = trim($_POST['mail_driver']                ?? '');
    $in['smtp_host']                  = trim($_POST['smtp_host']                  ?? '');
    $in['smtp_port']                  = (int) ($_POST['smtp_port']                  ?? 0);
    $in['smtp_encryption']            = trim($_POST['smtp_encryption']            ?? '');
    $in['smtp_user']                  = trim($_POST['smtp_user']                  ?? '');
    $in['smtp_pass']                  = trim($_POST['smtp_pass']                  ?? '');
    $in['mail_from_address']          = trim($_POST['mail_from_address']          ?? '');
    $in['mail_from_name']             = trim($_POST['mail_from_name']             ?? '');
    $in['mail_reply_to']              = trim($_POST['mail_reply_to']              ?? '');
    $in['allow_registration']         = isset($_POST['allow_registration']) ? 1 : 0;
    $in['require_email_confirm']      = isset($_POST['require_email_confirm']) ? 1 : 0;
    $in['default_user_role']          = trim($_POST['default_user_role']          ?? '');
    $in['password_min_length']        = (int) ($_POST['password_min_length']        ?? 0);
    $in['account_expiry_days']        = (int) ($_POST['account_expiry_days']        ?? 0);
    $in['max_accounts_per_ip']        = (int) ($_POST['max_accounts_per_ip']        ?? 0);
    $in['captcha_site_key']           = trim($_POST['captcha_site_key']           ?? '');
    $in['captcha_secret_key']         = trim($_POST['captcha_secret_key']         ?? '');
    $in['force_https']                = isset($_POST['force_https']) ? 1 : 0;
    $in['csp_policy']                 = trim($_POST['csp_policy']                 ?? '');
    $in['hsts_max_age']               = (int) ($_POST['hsts_max_age']               ?? 0);
    $in['session_timeout']            = (int) ($_POST['session_timeout']            ?? 0);
    $in['enable_2fa']                 = isset($_POST['enable_2fa']) ? 1 : 0;
    $in['ip_whitelist']               = trim($_POST['ip_whitelist']               ?? '');
    $in['ip_blacklist']               = trim($_POST['ip_blacklist']               ?? '');
    $in['ga_tracking_id']             = trim($_POST['ga_tracking_id']             ?? '');
    $in['gtm_id']                     = trim($_POST['gtm_id']                     ?? '');
    $in['chat_widget_code']           = trim($_POST['chat_widget_code']           ?? '');
    $in['social_facebook']            = trim($_POST['social_facebook']            ?? '');
    $in['social_twitter']             = trim($_POST['social_twitter']             ?? '');
    $in['social_linkedin']            = trim($_POST['social_linkedin']            ?? '');
    $in['enable_page_cache']          = isset($_POST['enable_page_cache']) ? 1 : 0;
    $in['cache_duration']             = (int) ($_POST['cache_duration']             ?? 0);
    $in['cdn_base_url']               = trim($_POST['cdn_base_url']               ?? '');
    $in['terms_of_service_url']       = trim($_POST['terms_of_service_url']       ?? '');
    $in['privacy_policy_url']         = trim($_POST['privacy_policy_url']         ?? '');
    $in['welcome_email_subject']      = trim($_POST['welcome_email_subject']      ?? '');
    $in['welcome_email_body']         = trim($_POST['welcome_email_body']         ?? '');
    $in['reset_password_email_subject'] = trim($_POST['reset_password_email_subject'] ?? '');
    $in['reset_password_email_body']  = trim($_POST['reset_password_email_body']  ?? '');
    $in['logo_url']                   = trim($_POST['logo_url']                   ?? '');
    $in['favicon_url']                = trim($_POST['favicon_url']                ?? '');
    $in['admin_email']                = trim($_POST['admin_email']                ?? '');
    $in['timezone']                   = trim($_POST['timezone']                   ?? '');
    $in['default_language']           = trim($_POST['default_language']           ?? '');
    $in['site_url']                   = trim($_POST['site_url']                   ?? '');
    $in['maintenance_mode']           = isset($_POST['maintenance_mode']) ? 1 : 0;
    $in['maintenance_message']        = trim($_POST['maintenance_message']        ?? '');

    if ($in['site_name'] === '') {
        $errors[] = 'Site Name cannot be empty.';
    }

    if (empty($errors)) {
        $sql = "
            UPDATE `demon_site_settings`
            SET
              site_name                = :site_name,
              site_tagline             = :site_tagline,
              site_description         = :site_description,
              site_author              = :site_author,
              site_robots              = :site_robots,
              mail_driver              = :mail_driver,
              smtp_host                = :smtp_host,
              smtp_port                = :smtp_port,
              smtp_encryption          = :smtp_encryption,
              smtp_user                = :smtp_user,
              smtp_pass                = :smtp_pass,
              mail_from_address        = :mail_from_address,
              mail_from_name           = :mail_from_name,
              mail_reply_to            = :mail_reply_to,
              allow_registration       = :allow_registration,
              require_email_confirm    = :require_email_confirm,
              default_user_role        = :default_user_role,
              password_min_length      = :password_min_length,
              account_expiry_days      = :account_expiry_days,
              max_accounts_per_ip      = :max_accounts_per_ip,
              captcha_site_key         = :captcha_site_key,
              captcha_secret_key       = :captcha_secret_key,
              force_https              = :force_https,
              csp_policy               = :csp_policy,
              hsts_max_age             = :hsts_max_age,
              session_timeout          = :session_timeout,
              enable_2fa               = :enable_2fa,
              ip_whitelist             = :ip_whitelist,
              ip_blacklist             = :ip_blacklist,
              ga_tracking_id           = :ga_tracking_id,
              gtm_id                   = :gtm_id,
              chat_widget_code         = :chat_widget_code,
              social_facebook          = :social_facebook,
              social_twitter           = :social_twitter,
              social_linkedin          = :social_linkedin,
              enable_page_cache        = :enable_page_cache,
              cache_duration           = :cache_duration,
              cdn_base_url             = :cdn_base_url,
              terms_of_service_url     = :terms_of_service_url,
              privacy_policy_url       = :privacy_policy_url,
              welcome_email_subject    = :welcome_email_subject,
              welcome_email_body       = :welcome_email_body,
              reset_password_email_subject = :reset_password_email_subject,
              reset_password_email_body = :reset_password_email_body,
              logo_url                 = :logo_url,
              favicon_url              = :favicon_url,
              admin_email              = :admin_email,
              timezone                 = :timezone,
              default_language         = :default_language,
              site_url                 = :site_url,
              maintenance_mode         = :maintenance_mode,
              maintenance_message      = :maintenance_message,
              updated_at               = NOW()
            WHERE id = :id
        ";
        $stmt = $pdo->prepare($sql);
        $params = [
            ':site_name'                  => $in['site_name'],
            ':site_tagline'               => $in['site_tagline'],
            ':site_description'           => $in['site_description'],
            ':site_author'                => $in['site_author'],
            ':site_robots'                => $in['site_robots'],
            ':mail_driver'                => $in['mail_driver'],
            ':smtp_host'                  => $in['smtp_host'],
            ':smtp_port'                  => $in['smtp_port'],
            ':smtp_encryption'            => $in['smtp_encryption'],
            ':smtp_user'                  => $in['smtp_user'],
            ':smtp_pass'                  => $in['smtp_pass'],
            ':mail_from_address'          => $in['mail_from_address'],
            ':mail_from_name'             => $in['mail_from_name'],
            ':mail_reply_to'              => $in['mail_reply_to'],
            ':allow_registration'         => $in['allow_registration'],
            ':require_email_confirm'      => $in['require_email_confirm'],
            ':default_user_role'          => $in['default_user_role'],
            ':password_min_length'        => $in['password_min_length'],
            ':account_expiry_days'        => $in['account_expiry_days'],
            ':max_accounts_per_ip'        => $in['max_accounts_per_ip'],
            ':captcha_site_key'           => $in['captcha_site_key'],
            ':captcha_secret_key'         => $in['captcha_secret_key'],
            ':force_https'                => $in['force_https'],
            ':csp_policy'                 => $in['csp_policy'],
            ':hsts_max_age'               => $in['hsts_max_age'],
            ':session_timeout'            => $in['session_timeout'],
            ':enable_2fa'                 => $in['enable_2fa'],
            ':ip_whitelist'               => $in['ip_whitelist'],
            ':ip_blacklist'               => $in['ip_blacklist'],
            ':ga_tracking_id'             => $in['ga_tracking_id'],
            ':gtm_id'                     => $in['gtm_id'],
            ':chat_widget_code'           => $in['chat_widget_code'],
            ':social_facebook'            => $in['social_facebook'],
            ':social_twitter'             => $in['social_twitter'],
            ':social_linkedin'            => $in['social_linkedin'],
            ':enable_page_cache'          => $in['enable_page_cache'],
            ':cache_duration'             => $in['cache_duration'],
            ':cdn_base_url'               => $in['cdn_base_url'],
            ':terms_of_service_url'       => $in['terms_of_service_url'],
            ':privacy_policy_url'         => $in['privacy_policy_url'],
            ':welcome_email_subject'      => $in['welcome_email_subject'],
            ':welcome_email_body'         => $in['welcome_email_body'],
            ':reset_password_email_subject' => $in['reset_password_email_subject'],
            ':reset_password_email_body'  => $in['reset_password_email_body'],
            ':logo_url'                   => $in['logo_url'],
            ':favicon_url'                => $in['favicon_url'],
            ':admin_email'                => $in['admin_email'],
            ':timezone'                   => $in['timezone'],
            ':default_language'           => $in['default_language'],
            ':site_url'                   => $in['site_url'],
            ':maintenance_mode'           => $in['maintenance_mode'],
            ':maintenance_message'        => $in['maintenance_message'],
            ':id'                         => $site['id']
        ];
        $stmt->execute($params);

        // Refresh $site
        $site = $pdo->query("SELECT * FROM `demon_site_settings` LIMIT 1")
                    ->fetch(PDO::FETCH_ASSOC);

        $successMessage = 'Settings have been updated successfully.';
    }
}

// Always re‐fetch current settings to populate form
$site = $pdo->query("SELECT * FROM `demon_site_settings` LIMIT 1")
            ->fetch(PDO::FETCH_ASSOC);
?>
<?php require __DIR__ . '/../inc/_global/views/head_start.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/head_end.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/page_start.php'; ?>

<div class="content">
  <h2 class="content-heading">Website Configuration</h2>

  <?php if (!empty($successMessage)): ?>
    <div class="alert alert-success">
      <?= htmlspecialchars($successMessage) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $err): ?>
          <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="row">
    <div class="col-lg-12">
      <div class="block block-rounded overflow-hidden">
        <ul class="nav nav-tabs nav-tabs-block align-items-center" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-general-tab" data-bs-toggle="tab" data-bs-target="#tab-general" role="tab" aria-controls="tab-general" aria-selected="true"><i class="fa-solid fa-toolbox"></i> General</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-email-tab" data-bs-toggle="tab" data-bs-target="#tab-email" role="tab" aria-controls="tab-email" aria-selected="false" tabindex="-1"><i class="fa-solid fa-envelope"></i> Email/SMPP</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-registration-tab" data-bs-toggle="tab" data-bs-target="#tab-registration" role="tab" aria-controls="tab-registration" aria-selected="false" tabindex="-1"><i class="fa-regular fa-address-card"></i> Registration</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-security-tab" data-bs-toggle="tab" data-bs-target="#tab-security" role="tab" aria-controls="tab-security" aria-selected="false" tabindex="-1"><i class="fa-solid fa-lock"></i> Security</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-advanced-tab" data-bs-toggle="tab" data-bs-target="#tab-advanced" role="tab" aria-controls="tab-advanced" aria-selected="false" tabindex="-1"><i class="fa-solid fa-scale-balanced"></i> Advanced</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-social-tab" data-bs-toggle="tab" data-bs-target="#tab-social" role="tab" aria-controls="tab-social" aria-selected="false" tabindex="-1"><i class="fa-solid fa-check-to-slot"></i> Social/Policies</button>
          </li>
          <li class="nav-item ms-auto">
            <div class="btn-group btn-group-sm pe-2">
              <button type="button" class="btn btn-alt-secondary" id="edit-btn">
                <i class="fa fa-pencil-alt"></i> Edit
              </button>
              <button type="button" class="btn btn-alt-primary d-none" id="save-btn" onclick="$('#settings-form').submit();">
                <i class="fa fa-save"></i> Save
              </button>
            </div>
          </li>
        </ul>
        <form id="settings-form" action="admin-config.php" method="POST">
          <input type="hidden" name="save_changes" value="1">
          <div class="block-content tab-content">

            <!-- General Tab -->
            <div class="tab-pane active" id="tab-general" role="tabpanel" aria-labelledby="tab-general-tab" tabindex="0">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label" for="site_name">Site Name</label>
                    <input type="text" class="form-control" id="site_name" name="site_name" value="<?= htmlspecialchars($site['site_name']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="site_tagline">Tagline</label>
                    <input type="text" class="form-control" id="site_tagline" name="site_tagline" value="<?= htmlspecialchars($site['site_tagline']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="site_description">Description</label>
                    <textarea class="form-control" id="site_description" name="site_description" rows="3" disabled><?= htmlspecialchars($site['site_description']) ?></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="site_author">Author</label>
                    <input type="text" class="form-control" id="site_author" name="site_author" value="<?= htmlspecialchars($site['site_author']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="site_robots">Robots Meta</label>
                    <input type="text" class="form-control" id="site_robots" name="site_robots" value="<?= htmlspecialchars($site['site_robots']) ?>" disabled>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label" for="site_url">Site URL</label>
                    <input type="text" class="form-control" id="site_url" name="site_url" value="<?= htmlspecialchars($site['site_url']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="logo_url">Logo URL</label>
                    <input type="text" class="form-control" id="logo_url" name="logo_url" value="<?= htmlspecialchars($site['logo_url']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="favicon_url">Favicon URL</label>
                    <input type="text" class="form-control" id="favicon_url" name="favicon_url" value="<?= htmlspecialchars($site['favicon_url']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="admin_email">Administrator Email</label>
                    <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?= htmlspecialchars($site['admin_email']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="default_language">Default Language</label>
                    <input type="text" class="form-control" id="default_language" name="default_language" value="<?= htmlspecialchars($site['default_language']) ?>" disabled>
                  </div>
                </div>
              </div>
            </div>

            <!-- Email/SMPP Tab -->
            <div class="tab-pane" id="tab-email" role="tabpanel" aria-labelledby="tab-email-tab" tabindex="0">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label" for="mail_driver">Mail Driver</label>
                    <input type="text" class="form-control" id="mail_driver" name="mail_driver" value="<?= htmlspecialchars($site['mail_driver']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="smtp_host">SMTP Host</label>
                    <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?= htmlspecialchars($site['smtp_host']) ?>" disabled>
                  </div>
                  <div class="row mb-3">
                    <div class="col-6">
                      <label class="form-label" for="smtp_port">SMTP Port</label>
                      <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="<?= htmlspecialchars($site['smtp_port']) ?>" disabled>
                    </div>
                    <div class="col-6">
                      <label class="form-label" for="smtp_encryption">Encryption</label>
                      <select class="form-select" id="smtp_encryption" name="smtp_encryption" disabled>
                        <?php foreach (['tls','ssl','none'] as $enc): ?>
                          <option value="<?= $enc ?>" <?= $site['smtp_encryption'] === $enc ? 'selected' : '' ?>><?= strtoupper($enc) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="smtp_user">SMTP Username</label>
                    <input type="text" class="form-control" id="smtp_user" name="smtp_user" value="<?= htmlspecialchars($site['smtp_user']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="smtp_pass">SMTP Password</label>
                    <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" value="<?= htmlspecialchars($site['smtp_pass']) ?>" disabled>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label" for="mail_from_address">Mail From Address</label>
                    <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" value="<?= htmlspecialchars($site['mail_from_address']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="mail_from_name">Mail From Name</label>
                    <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" value="<?= htmlspecialchars($site['mail_from_name']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="mail_reply_to">Mail Reply‐To</label>
                    <input type="email" class="form-control" id="mail_reply_to" name="mail_reply_to" value="<?= htmlspecialchars($site['mail_reply_to']) ?>" disabled>
                  </div>
                </div>
              </div>
            </div>

            <!-- Registration Tab -->
            <div class="tab-pane" id="tab-registration" role="tabpanel" aria-labelledby="tab-registration-tab" tabindex="0">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="allow_registration" name="allow_registration" <?= $site['allow_registration'] ? 'checked' : '' ?> disabled>
                    <label class="form-check-label" for="allow_registration">Allow Registration</label>
                  </div>
                  <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="require_email_confirm" name="require_email_confirm" <?= $site['require_email_confirm'] ? 'checked' : '' ?> disabled>
                    <label class="form-check-label" for="require_email_confirm">Require Email Confirmation</label>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="default_user_role">Default User Role</label>
                    <select class="form-select" id="default_user_role" name="default_user_role" disabled>
                      <?php foreach ($allRoles as $r): ?>
                        <option value="<?= htmlspecialchars($r) ?>" <?= $site['default_user_role'] === $r ? 'selected' : '' ?>><?= ucfirst(htmlspecialchars($r)) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label" for="password_min_length">Min Password Length</label>
                    <input type="number" class="form-control" id="password_min_length" name="password_min_length" min="4" max="64" value="<?= htmlspecialchars($site['password_min_length']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="account_expiry_days">Account Expiry (days)</label>
                    <input type="number" class="form-control" id="account_expiry_days" name="account_expiry_days" min="0" value="<?= htmlspecialchars($site['account_expiry_days']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="max_accounts_per_ip">Max Accounts per IP</label>
                    <input type="number" class="form-control" id="max_accounts_per_ip" name="max_accounts_per_ip" min="1" value="<?= htmlspecialchars($site['max_accounts_per_ip']) ?>" disabled>
                  </div>
                </div>
              </div>
            </div>

            <!-- Security Tab -->
            <div class="tab-pane" id="tab-security" role="tabpanel" aria-labelledby="tab-security-tab" tabindex="0">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label" for="captcha_site_key">reCAPTCHA Site Key</label>
                    <input type="text" class="form-control" id="captcha_site_key" name="captcha_site_key" value="<?= htmlspecialchars($site['captcha_site_key']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="captcha_secret_key">reCAPTCHA Secret Key</label>
                    <input type="text" class="form-control" id="captcha_secret_key" name="captcha_secret_key" value="<?= htmlspecialchars($site['captcha_secret_key']) ?>" disabled>
                  </div>
                  <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="force_https" name="force_https" <?= $site['force_https'] ? 'checked' : '' ?> disabled>
                    <label class="form-check-label" for="force_https">Force HTTPS</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label" for="csp_policy">Content Security Policy</label>
                    <textarea class="form-control" id="csp_policy" name="csp_policy" rows="2" disabled><?= htmlspecialchars($site['csp_policy']) ?></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="hsts_max_age">HSTS Max‐Age (seconds)</label>
                    <input type="number" class="form-control" id="hsts_max_age" name="hsts_max_age" min="0" value="<?= htmlspecialchars($site['hsts_max_age']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="session_timeout">Session Timeout (minutes)</label>
                    <input type="number" class="form-control" id="session_timeout" name="session_timeout" min="1" value="<?= htmlspecialchars($site['session_timeout']) ?>" disabled>
                  </div>
                  <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="enable_2fa" name="enable_2fa" <?= $site['enable_2fa'] ? 'checked' : '' ?> disabled>
                    <label class="form-check-label" for="enable_2fa">Enable Two‐Factor Authentication</label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Advanced Tab -->
            <div class="tab-pane" id="tab-advanced" role="tabpanel" aria-labelledby="tab-advanced-tab" tabindex="0">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label" for="ip_whitelist">IP Whitelist (one per line)</label>
                    <textarea class="form-control" id="ip_whitelist" name="ip_whitelist" rows="2" disabled><?= htmlspecialchars($site['ip_whitelist']) ?></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="ip_blacklist">IP Blacklist (one per line)</label>
                    <textarea class="form-control" id="ip_blacklist" name="ip_blacklist" rows="2" disabled><?= htmlspecialchars($site['ip_blacklist']) ?></textarea>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label" for="ga_tracking_id">Google Analytics ID</label>
                    <input type="text" class="form-control" id="ga_tracking_id" name="ga_tracking_id" value="<?= htmlspecialchars($site['ga_tracking_id']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="gtm_id">Google Tag Manager ID</label>
                    <input type="text" class="form-control" id="gtm_id" name="gtm_id" value="<?= htmlspecialchars($site['gtm_id']) ?>" disabled>
                  </div>
                  <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="enable_page_cache" name="enable_page_cache" <?= $site['enable_page_cache'] ? 'checked' : '' ?> disabled>
                    <label class="form-check-label" for="enable_page_cache">Enable Page Cache</label>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="cache_duration">Cache Duration (seconds)</label>
                    <input type="number" class="form-control" id="cache_duration" name="cache_duration" min="0" value="<?= htmlspecialchars($site['cache_duration']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="cdn_base_url">CDN Base URL</label>
                    <input type="text" class="form-control" id="cdn_base_url" name="cdn_base_url" value="<?= htmlspecialchars($site['cdn_base_url']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="chat_widget_code">Chat Widget Embed Code</label>
                    <textarea class="form-control" id="chat_widget_code" name="chat_widget_code" rows="2" disabled><?= htmlspecialchars($site['chat_widget_code']) ?></textarea>
                  </div>
                </div>
              </div>
            </div>

            <!-- Social/Policies Tab -->
            <div class="tab-pane" id="tab-social" role="tabpanel" aria-labelledby="tab-social-tab" tabindex="0">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label" for="social_facebook">Facebook URL</label>
                    <input type="text" class="form-control" id="social_facebook" name="social_facebook" value="<?= htmlspecialchars($site['social_facebook']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="social_twitter">Twitter URL</label>
                    <input type="text" class="form-control" id="social_twitter" name="social_twitter" value="<?= htmlspecialchars($site['social_twitter']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="social_linkedin">LinkedIn URL</label>
                    <input type="text" class="form-control" id="social_linkedin" name="social_linkedin" value="<?= htmlspecialchars($site['social_linkedin']) ?>" disabled>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label" for="terms_of_service_url">Terms of Service URL</label>
                    <input type="text" class="form-control" id="terms_of_service_url" name="terms_of_service_url" value="<?= htmlspecialchars($site['terms_of_service_url']) ?>" disabled>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="privacy_policy_url">Privacy Policy URL</label>
                    <input type="text" class="form-control" id="privacy_policy_url" name="privacy_policy_url" value="<?= htmlspecialchars($site['privacy_policy_url']) ?>" disabled>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="maintenance_mode" name="maintenance_mode" <?= $site['maintenance_mode'] ? 'checked' : '' ?> disabled>
                    <label class="form-check-label" for="maintenance_mode">Maintenance Mode</label>
                  </div>
                  <div class="mb-3">
                    <label class="form-label" for="maintenance_message">Maintenance Message</label>
                    <textarea class="form-control" id="maintenance_message" name="maintenance_message" rows="2" disabled><?= htmlspecialchars($site['maintenance_message']) ?></textarea>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  // When “Edit” is clicked, enable all form inputs & swap buttons
  document.getElementById('edit-btn').addEventListener('click', function() {
    var form = document.getElementById('settings-form');
    Array.from(form.querySelectorAll('input, textarea, select')).forEach(function(el) {
      el.removeAttribute('disabled');
    });
    document.getElementById('edit-btn').classList.add('d-none');
    document.getElementById('save-btn').classList.remove('d-none');
  });
</script>

<?php require __DIR__ . '/../inc/_global/views/page_end.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/footer_start.php'; ?>
<?php require __DIR__ . '/../inc/_global/views/footer_end.php'; ?>
