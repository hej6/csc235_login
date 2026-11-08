<?php
// Database initialization component. Execute through php/start.php only.
if (!defined('DB_SETUP_RUNNING')) {
    http_response_code(403);
    exit('Run php/start.php to initialize the database.');
}

/* Users */
$create_users = $db_connection->prepare(
	"CREATE OR REPLACE TABLE Users(
        user_id int NOT NULL AUTO_INCREMENT,
        role_id int NOT NULL,
        contact_id int NOT NULL,
	    creation_date timestamp,
	    PRIMARY KEY(user_id),
        FOREIGN KEY(role_id) REFERENCES Roles(role_id),
        FOREIGN KEY(contact_id) REFERENCES Contacts(contact_id));");
$create_users->execute();
$create_users->close();

/* Users */
$insert_users = $db_connection->prepare(
	"INSERT INTO Users
		(user_id, role_id, contact_id, creation_date) VALUES(?,?,?,?);");
$insert_users->bind_param("iiis", $user_id, $role_id, $contact_id, $creation_date);
$user_id = 1;
$role_id = 1;
$contact_id = 1;
$creation_date = date("Y-m-d H:i:s");
$insert_users->execute();

$user_id = 2;
$role_id = 2;
$contact_id = 2;
$creation_date = date("Y-m-d H:i:s");
$insert_users->execute();

$user_id = 3;
$role_id = 3;
$contact_id = 3;
$creation_date = date("Y-m-d H:i:s");
$insert_users->execute();

$user_id = 4;
$role_id = 1;
$contact_id = 4;
$creation_date = date("Y-m-d H:i:s");
$insert_users->execute();

$insert_users->close();
