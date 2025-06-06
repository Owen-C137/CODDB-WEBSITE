<?php
/**
 * backend/views/inc_sidebar_mini_nav.php
 *
 * Author: pixelcave
 *
 * An example sidebar showcasing a mini vertical navigation along with the main navigation
 *
 */
?>

<!-- Sidebar -->
<!--
    Sidebar Mini Mode is not supported with mini navigation
-->
<nav id="sidebar" class="with-mini-nav">
  <!-- Sidebar Mini Nav -->
  <div class="sidebar-mini-nav">
    <!-- Logo -->
    <a href="index.php" class="mini-nav-item h-header">
      <i class="fa fa-fire text-primary"></i>
    </a>
    <!-- END Logo -->

    <!-- Mini Main Nav -->
    <nav class="flex-grow-1 space-y-1">
      <a class="mini-nav-item" href="javascript:void(0)">
        <i class="fa fa-box-archive fs-sm"></i>
      </a>
      <a class="mini-nav-item" href="javascript:void(0)">
        <i class="fa fa-calendar fs-sm"></i>
      </a>
      <a class="mini-nav-item" href="javascript:void(0)">
        <i class="fa fa-users-line fs-sm"></i>
      </a>
    </nav>
    <!-- END Mini Main Nav -->

    <!-- Mini User Nav -->
    <nav class="space-y-1 pb-2">
      <a class="mini-nav-item" href="be_pages_generic_profile.php">
        <i class="fa fa-user-gear fs-sm"></i>
      </a>
      <a class="mini-nav-item" href="op_auth_signin.php">
        <i class="fa fa-lock fs-sm"></i>
      </a>
    </nav>
    <!-- END Mini User Nav -->
  </div>
  <!-- END Sidebar Mini Nav -->

  <!-- Sidebar Scrolling -->
  <div class="js-sidebar-scroll full-height">
    <!-- Sidebar Content -->
    <div class="sidebar-content">
      <!-- Side Header -->
      <div class="content-header justify-content-lg-center">
        <!-- Logo -->
        <div>
          <span class="smini-visible fw-bold tracking-wide fs-lg">
            c<span class="text-primary">b</span>
          </span>
          <a class="link-fx fw-bold tracking-wide mx-auto" href="index.php">
            <span class="smini-hidden">
              <span class="fs-4 text-dual">code</span><span class="fs-4 text-primary">base</span>
            </span>
          </a>
        </div>
        <!-- END Logo -->

        <!-- Options -->
        <div>
          <!-- Close Sidebar, Visible only on mobile screens -->
          <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
          <button type="button" class="btn btn-sm btn-alt-danger d-lg-none" data-toggle="layout" data-action="sidebar_close">
            <i class="fa fa-fw fa-times"></i>
          </button>
          <!-- END Close Sidebar -->
        </div>
        <!-- END Options -->
      </div>
      <!-- END Side Header -->

      <!-- Side User -->
      <div class="content-side content-side-user px-0 py-0">
        <!-- Visible only in mini mode -->
        <div class="smini-visible-block animated fadeIn px-3">
          <?php $cb->get_avatar(15, '', 32); ?>
        </div>
        <!-- END Visible only in mini mode -->

        <!-- Visible only in normal mode -->
        <div class="smini-hidden text-center mx-auto">
          <a class="img-link" href="be_pages_generic_profile.php">
            <?php $cb->get_avatar(15); ?>
          </a>
          <ul class="list-inline mt-3 mb-0">
            <li class="list-inline-item">
              <a class="link-fx text-dual fs-sm fw-semibold text-uppercase" href="be_pages_generic_profile.php">J. Smith</a>
            </li>
            <li class="list-inline-item">
              <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
              <a class="link-fx text-dual" data-toggle="layout" data-action="dark_mode_toggle" href="javascript:void(0)">
                <i class="fa fa-burn"></i>
              </a>
            </li>
            <li class="list-inline-item">
              <a class="link-fx text-dual" href="op_auth_signin.php">
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
