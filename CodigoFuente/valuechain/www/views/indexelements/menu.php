<?php
//INCLUYE LOS ARCHIVOS NECESARIOS
include_once("../includes/conf.php");
?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="index3.html" class="brand-link">
    <img src="<?php echo PROJECT_ROOT . "/views/dist/img/AdminLTELogo.png" ?> " alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light">PuzzleMesh</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="info">
        <a href="#" class="d-block"><?php echo $_SESSION['username'] . "(" . $_SESSION['acronym'] . ")" ?></a>
      </div>
    </div>

    <!-- SidebarSearch Form -->


    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class
            with font-awesome or any other icon font library -->
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-puzzle-piece"></i>
            <p>
              Puzzles
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo PROJECT_ROOT; ?>/pages/wizard.php" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Create a puzzle</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo PROJECT_ROOT; ?>/pages/list_puzzles.php" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>List puzzles</p>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-folder"></i>
            <p>
              Storage
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo PROJECT_ROOT; ?>/pages/catalogs/catalogs.php" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Catalogs</p>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>