<nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
  <div class="container-fluid">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
      <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
        <i class="bx bx-menu bx-sm"></i>
      </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
      <!-- Search -->
      <?php include("search-largscreen.php"); ?>

      <!-- /Search -->

      <ul class="navbar-nav flex-row align-items-center ms-auto">
        <!-- Language -->
        <!--/ Language -->

        <!-- Style Switcher -->
        <?php include("style-switcher.php"); ?>
        <!--/ Style Switcher -->

        <!-- bascket  -->
                 <?php include("bascket.php"); ?>

        <!-- bascket -->

        <!-- Notification -->
        <?php //include("notification.php"); ?>
        <!--/ Notification -->

        <!-- User -->
        <?php include("profile-menu.php"); ?>
        <!--/ User -->
      </ul>
    </div>

    <!-- Search Small Screens -->
    <div class="navbar-search-wrapper search-input-wrapper d-none">
      <input type="text" class="form-control search-input container-fluid border-0" placeholder="جستجو ..."
        aria-label="Search...">
      <i class="bx bx-x bx-sm search-toggler cursor-pointer"></i>
    </div>
  </div>
</nav>