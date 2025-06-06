<?php
/**
 * page_start.php
 *
 * Author: pixelcave
 *
 * The start section of each page used in every page of the template.
 */

// At this point, $cb should already be defined by your global config.
?>
<!-- Page Container -->
<div id="page-container"<?php $cb->page_classes(); ?>>
  <?php if (!empty($cb->page_loader)) { ?>
    <!-- Page loader (if enabled in $cb) -->
    <div id="page-loader" class="show"></div>
  <?php } ?>

  <?php
    // Only attempt to include these if the file actually exists on disk.
    if (!empty($cb->inc_side_overlay) && file_exists($cb->inc_side_overlay)) {
        include $cb->inc_side_overlay;
    }
    if (!empty($cb->inc_sidebar) && file_exists($cb->inc_sidebar)) {
        include $cb->inc_sidebar;
    }
    if (!empty($cb->inc_header) && file_exists($cb->inc_header)) {
        include $cb->inc_header;
    }
  ?>

  <!-- Main Container -->
  <main id="main-container">
