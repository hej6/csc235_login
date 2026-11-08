<?php
// Database initialization component. Execute through php/start.php only.
if (!defined('DB_SETUP_RUNNING')) {
    http_response_code(403);
    exit('Run php/start.php to initialize the database.');
}

/* Role */
$create_roles = $db_connection->prepare(
	"CREATE OR REPLACE TABLE Roles(
        role_id int NOT NULL AUTO_INCREMENT,
        role_type varchar(255) NOT NULL,
        PRIMARY KEY(role_id));");
$create_roles->execute();
$create_roles->close();

/* Roles */
$insert_role = $db_connection->prepare(
	"INSERT INTO Roles
    	(role_id, role_type) VALUES(?,?);");
$insert_role->bind_param("is", $role_id, $role_title);

$role_id = 1;
$role_title = "administrator";
$insert_role->execute();

$role_id = 2;
$role_title = "user";
$insert_role->execute();

$role_id = 3;
$role_title = "guest";
$insert_role->execute();

$insert_role->close();
