<?php
/**
 * User Index Page
 *
 * @author    Jackie Isabella <connorj4@southernct.edu>
 * @copyright 2026 
 * @date      2026-02-24
 * @version   1.0
 */
error_reporting(E_ALL); // Report all errors
ini_set('display_errors', 0); // set to 1 to display errors, 0 to hide them

  /* Quick Paths */
  /* note the 2 after __FILE__, because it's 2 directories deep */
  include_once (realpath(dirname(__FILE__, 2).'/php/session.php')); // Session will be included in header.php
  include_once (realpath(dirname(__FILE__, 2).'/php/path.php')); // Path will be included in header.php
  // Session will be included in header.php

  /* Page Name */
  $page_name = "user";

?>
<?php
//======================================================================
// USER DASHBOARD PAGE
//======================================================================

error_reporting(E_ALL);
ini_set('display_errors', 0); // set to 1 to display errors, 0 to hide them

  /* Quick Paths */
  /* note the 2 after __FILE__, because it's 2 directories deep */
  include_once (realpath(dirname(__FILE__, 2).'/php/session.php')); // Session will be included in header.php
  include_once (realpath(dirname(__FILE__, 2).'/php/path.php')); // Path will be included in header.php
  // Session will be included in header.php
  
  /* Check Role */
  include_once (ROOT_SRC_PATH .'/check_user.php'); // Check user role and redirect if not authorized

  $user_check = $_SESSION['login_user'];
  // Check user and get roll session from database

  /* Page Name */
  $page_name = "User ORG Chart Landing"; // Set page name for active link in header

// ****User landing with Banner and Cards for Staff, Colleges, Classifications, and Account Info******/
include_once (ROOT_PATH . '/php/config.php');
$staff_count   = $db_connection->query("SELECT COUNT(*) AS total FROM Staff")->fetch_assoc()['total'];
$dept_count    = $db_connection->query("SELECT COUNT(*) AS total FROM Department")->fetch_assoc()['total'];
$class_count   = $db_connection->query("SELECT COUNT(*) AS total FROM Classification")->fetch_assoc()['total'];
$div_count     = $db_connection->query("SELECT COUNT(*) AS total FROM Organization")->fetch_assoc()['total'];
$college_count = $db_connection->query("SELECT COUNT(*) AS total FROM College")->fetch_assoc()['total'];
?>
<!doctype html>
<html lang="en">
  <head>
  <?php include_once (ROOT_PATH . '/include/head.php'); // Include head.php ?>
  </head>
  <body class="<?php echo $page_name; ?>">

  <?php include_once (ROOT_PATH . '/include/header.php'); // Include header.php ?>
    <main role="main" class="container">

      <!-- Banner -->
      <div class="card text-bg-dark overflow-hidden shadow mb-4">
        <img src="/csc235_login/img/banner.jpg" class="card-img card-img-banner" alt="Campus">
        <div class="card-img-overlay d-flex flex-column justify-content-center align-items-center text-center overlay-dark">
          <h1 class="display-4">Welcome, <?php echo htmlspecialchars($_SESSION['user_first']); ?></h1>
          <p class="lead">Explore who's who at Wossamotta U! </p>
        </div>
      </div>

      <!-- Cards -->
      <div class="row g-4">

        <div class="col-md-4">
          <a href="/csc235_login/user/staff.php" class="text-decoration-none">
            <div class="card text-bg-dark overflow-hidden shadow">
              <img src="/csc235_login/img/staff.jpg" class="card-img card-img-tile" alt="Staff Directory">
              <div class="card-img-overlay d-flex flex-column justify-content-end overlay-light">
                <div class="text-band">
                  <h4 class="card-title">Staff Directory</h4>
                  <p class="card-text mb-0"><?php echo $staff_count; ?> staff members</p>
                </div>
              </div>
            </div>
          </a>
        </div>

        <div class="col-md-4">
          <a href="/csc235_login/user/colleges.php" class="text-decoration-none">
            <div class="card text-bg-dark overflow-hidden shadow">
              <img src="/csc235_login/img/colleges.jpg" class="card-img card-img-tile" alt="Divisions and Departments">
              <div class="card-img-overlay d-flex flex-column justify-content-end overlay-light">
                <div class="text-band">
                  <h4 class="card-title">Divisions &amp; Departments</h4>
                  <p class="card-text mb-0"><?php echo $div_count; ?> divisions, <?php echo $college_count; ?> colleges, <?php echo $dept_count; ?> departments</p>
                </div>
              </div>
            </div>
          </a>
        </div>

        <div class="col-md-4">
          <a href="/csc235_login/user/classifications.php" class="text-decoration-none">
            <div class="card text-bg-dark overflow-hidden shadow">
              <img src="/csc235_login/img/classifications.jpg" class="card-img card-img-tile" alt="Classifications and Ranks">
              <div class="card-img-overlay d-flex flex-column justify-content-end overlay-light">
                <div class="text-band">
                  <h4 class="card-title">Classifications &amp; Ranks</h4>
                  <p class="card-text mb-0"><?php echo $class_count; ?> classifications</p>
                </div>
              </div>
            </div>
          </a>
        </div>

      </div>
      <!-- Your Account -->
      <hr class="my-5">
      <section class="mb-4 text-center">
        <h2>Your Account</h2>
        <table class="table">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">First</th>
              <th scope="col">Last</th>
              <th scope="col">Email</th>
              <th scope="col">Username</th>
              <th scope="col">Role</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $stmt = $db_connection->prepare(
                "SELECT u.user_id, c.first_name, c.last_name, c.email, cr.username, r.role_type
                 FROM Users u
                 INNER JOIN Contacts c ON u.contact_id = c.contact_id
                 INNER JOIN Credentials cr ON u.user_id = cr.user_id
                 INNER JOIN Roles r ON u.role_id = r.role_id
                 WHERE cr.username = ?"
              );
              $stmt->bind_param("s", $user_check);
              $stmt->execute();
              $result = $stmt->get_result();

              if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<th scope='row'>" . $row["user_id"] . "</th>";
                  echo "<td>" . htmlspecialchars($row["first_name"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["last_name"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["role_type"]) . "</td>";
                  echo "</tr>";
                }
              } else {
                echo "<tr><td colspan='6'>No account found</td></tr>";
              }
              $stmt->close();
              $db_connection->close();
            ?>
          </tbody>
        </table>
      </section>

    </main>
    <?php include_once (ROOT_PATH . '/include/footer.php'); ?>
  </body>
</html>