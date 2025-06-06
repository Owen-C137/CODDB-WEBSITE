<?php
/**
 * backend/views/inc_footer.php
 *
 * Author: pixelcave
 *
 * The footer of each page
 *
 */

// Assuming config.php has already been included and $site (or individual variables) is available
// Example variables coming from config.php:
//   $site_name
//   $site_url
//   $site_author
?>
<!-- Footer -->
<footer id="page-footer">
  <div class="content py-3">
    <div class="row fs-sm">
      <div class="col-sm-6 order-sm-2 py-1 text-center text-sm-end">
        Craftеd with <i class="fa fa-heart text-danger"></i> by
        <?php if (!empty($site_author) && !empty($site_url)): ?>
          <a class="fw-semibold" href="<?php echo htmlspecialchars($site_url); ?>" target="_blank">
            <?php echo htmlspecialchars($site_author); ?>
          </a>
        <?php elseif (!empty($site_author)): ?>
          <?php echo htmlspecialchars($site_author); ?>
        <?php else: ?>
          Your Company
        <?php endif; ?>
      </div>
      <div class="col-sm-6 order-sm-1 py-1 text-center text-sm-start">
        <?php if (!empty($site_name) && !empty($site_url)): ?>
          <a class="fw-semibold" href="<?php echo htmlspecialchars($site_url); ?>" target="_blank">
            <?php echo htmlspecialchars($site_name); ?>
          </a>
        <?php elseif (!empty($site_name)): ?>
          <?php echo htmlspecialchars($site_name); ?>
        <?php else: ?>
          Website
        <?php endif; ?>
        &copy; <span><?php echo date('Y'); ?></span>
      </div>
    </div>
  </div>
</footer>
<!-- END Footer -->
