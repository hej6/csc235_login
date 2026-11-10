<?php

/**
 * Check Admin Role
 *
 * @author    Miguel, Jackie, Jared
 * @copyright 2026 
 * @date      2026-02-26
 * @version   1.0
 */

// Check if user role is 1 (admin user)

if($user_role == 1){
  // Everything is working
  echo '<script>console.log("Everything is Working");</script>';
} else {
  // Access Denied
  $_SESSION['message'] = "Access Denied";
  // Redirect to logout
  header("location: " . SRC_PATH . "/logout.php");
  exit;
}

?>