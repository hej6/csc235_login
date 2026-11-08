<?php
// Database initialization component. Execute through php/start.php only.
if (!defined('DB_SETUP_RUNNING')) {
    http_response_code(403);
    exit('Run php/start.php to initialize the database.');
}

/* Credentials */
$create_credentials = $db_connection->prepare(
	"CREATE OR REPLACE TABLE Credentials(
        username varchar(255) NOT NULL,
	    user_id int NOT NULL,
        password_salted varchar(255) NOT NULL,
	    PRIMARY KEY(username),
        FOREIGN KEY(user_id) REFERENCES Users(user_id));");
$create_credentials->execute();
$create_credentials->close();

/* Credentials */
$insert_credentials = $db_connection->prepare(
	"INSERT INTO Credentials
		(username, user_id, password_salted) VALUES(?,?,?);");
$insert_credentials->bind_param("sis", $username, $user_id, $password_salted);
$username = "johndoe";
$user_id = 1;
$password_salted = crypt("SCSU2024", $salt);
//$password_salted = password_hash("password123", PASSWORD_DEFAULT);
$insert_credentials->execute();

$user_id = 2;
$username = "janesmith";
$password_salted = crypt("SCSU2025", $salt);
//$password_salted = password_hash("mypassword", PASSWORD_DEFAULT);
$insert_credentials->execute();

$user_id = 3;
$username = "charliebrown";
$password_salted = crypt("SCSU2026", $salt);
//$password_salted = password_hash("charlie123", PASSWORD_DEFAULT);
$insert_credentials->execute();

$user_id = 4;
$username = "jjmCSC540";
$password_salted = crypt("SCSU2026", $salt);
//$password_salted = password_hash("admin123", PASSWORD_DEFAULT);
$insert_credentials->execute();

$insert_credentials->close();
