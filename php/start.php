<?php
/**
 * Create the database and tables for the login system
 *
 * @author    Joshua Connor <connorj4@southernct.edu>
 * @copyright 2026 
 * @date      2026-02-24
 * @version   1.0
 */

// Development-only database reset script. Running this deletes local table data.
error_reporting(-1);
ini_set('display_errors', 'On');

require_once __DIR__ . '/config.local.php';

// Establish a connection to the local MySQL server.
try {
    $db_connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);
} catch (mysqli_sql_exception $e) {
    die('Connection failed: ' . $e->getMessage());
}

// Create the database defined in config.local.php if needed
$create_stmt = "CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '``', DB_NAME) . "`";
$db_connection->query($create_stmt);
echo nl2br("Database was successfully created or already exists.\r\n");

// Select the application database, then reset its tables
$db_connection->select_db(DB_NAME);

/* Drop all tables for clean install */
$db_connection->query('SET foreign_key_checks = 0');
if ($result = $db_connection->query("SHOW TABLES")) {
  while($row = $result->fetch_array(MYSQLI_NUM)) {
    $db_connection->query('DROP TABLE IF EXISTS '.$row[0]);
	}
	echo "Tables removed successfully." . nl2br("\r\n");
} else {
	echo "No tables were removed." . nl2br("\r\n");
}
$db_connection->query('SET foreign_key_checks = 1');

$salt = SALT;
define('DB_SETUP_RUNNING', true);
echo nl2br("Table creation and seed data initialization started.\r\n");

/* Execute in dependency order: referenced tables and records first; Organized by TABLE */

 // USERS (Login to website)
require __DIR__ . '/database/roles.php';
require __DIR__ . '/database/contacts.php';
require __DIR__ . '/database/users.php';
require __DIR__ . '/database/credentials.php';

// STAFF (Org Chart)
require __DIR__ . '/database/classification.php';
require __DIR__ . '/database/organization.php';
require __DIR__ . '/database/ranks.php';
require __DIR__ . '/database/job.php';
require __DIR__ . '/database/college.php';
require __DIR__ . '/database/building.php';
require __DIR__ . '/database/department.php';
require __DIR__ . '/database/staff.php';


// Status message
echo nl2br("Database tables were successfully created and populated.\r\n");
header('Refresh: 10; url=/csc235_login');
$db_connection->close();
