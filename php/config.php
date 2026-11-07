<?php
/**
 * Database Configuration File
 *
 * @author    Joshua Connor <connorj4@southernct.edu>
 * @copyright 2026 
 * @date      2026-02-24
 * @version   1.0
 */

// Author: Joshua Connor - Added new validation logic (2026-02-24)
// To use this configuration file, make sure to set the correct
// database connection parameters below.  

include_once (ROOT_PATH.'/php/path.php');
include_once (ROOT_PATH.'/php/reporting.php');
include_once (ROOT_PATH.'/php/config.local.php');



try {
  $db_connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
    OR die("Connection failed: " . $db_connection->connect_error);
} catch (Exception $e) {
  echo 'Caught exception: ',  $e->getMessage(), "\n";
} 
  
?>