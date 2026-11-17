<?php
/**
 * Header Include File
 *
 * @author    Jackie Isabella <
 * @copyright 2026 
 * @date      2026-11-16
 * @version   2.0
 */
// 
if (!isset($_SESSION)) {
    // Only include session if not already started
    include_once (realpath(dirname(__FILE__).'/path.php'));
    // Include session file
    include_once (ROOT_PATH . '/php/session.php');
}
?>
<?php
// Header Section
?>
<header class="mb-5">
<?php
// navigation bar
?>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="../">   
    <i class="bi bi-claude"></i>  Basic Login</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php
          /* Show Home link only when user is NOT logged in */
          if (!isset($_SESSION['user_name'])) {
            echo '<li class="nav-item">';
            echo '<a class="nav-link active" aria-current="page" href="../">Home</a>';
            echo '</li>';
          }
        ?>
        <?php
          /* User Information */
          if (isset($_SESSION['user_name']) && $_SESSION['user_role'] == 1) { // Only display when user is logged in
            echo '<li class="nav-item"><a class="nav-link" href="index.php">Admin Dashboard</a></li>';
            echo '<li class="nav-item"><a class="nav-link" href="#">New Page</a></li>';
          }
        ?>
        <?php
          /* User Information */
          if (isset($_SESSION['user_name']) && $_SESSION['user_role'] == 2) {
            echo '<li class="nav-item"><a class="nav-link" href="/org_chart/user/index.php">User Dashboard</a></li>';
            echo '<li class="nav-item dropdown">';
            echo '<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Browse</a>';
            echo '<ul class="dropdown-menu">';
            echo '<li><a class="dropdown-item" href="/org_chart/user/staff.php">Staff Directory</a></li>';
            echo '<li><a class="dropdown-item" href="/org_chart/user/colleges.php">Divisions, Colleges &amp; Departments</a></li>';
            echo '<li><a class="dropdown-item" href="/org_chart/user/classifications.php">Classifications &amp; Ranks</a></li>';
            echo '</ul>';
            echo '</li>';
         }
        ?>
        <?php
          /* User Information */
          if (isset($_SESSION['user_name']) && $_SESSION['user_role'] == 3) { // Only display when user is logged in
            echo '<li class="nav-item"><a class="nav-link" href="index.php">Guest Dashboard</a></li>';
            echo '<li class="nav-item"><a class="nav-link" href="#">New Page</a></li>';
          }
        ?>
      </ul>
      <span class="navbar-text">
        <?php
            /* User Information */
            if (isset($_SESSION['user_first']) && isset($_SESSION['user_type'])) {
                echo 'Welcome ' . $_SESSION['user_first'] . ' (' . $_SESSION['user_type'] . ')';
                echo ' | <a href="../php/logout.php">Logout</a>';
            } else {
                echo 'Not Logged In';
            }
            ?>
      </span>
    </div>
  </div>
</nav>
</header>