<?php
// Database initialization component. Execute through php/start.php only.
if (!defined('DB_SETUP_RUNNING')) {
    http_response_code(403);
    exit('Run php/start.php to initialize the database.');
}

/* Contacts */
$create_contacts = $db_connection->prepare(
	"CREATE OR REPLACE TABLE Contacts(
        contact_id int NOT NULL AUTO_INCREMENT,
        last_name varchar(255) NOT NULL,
        first_name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(255) NOT NULL,
        street_1 varchar(255),
        street_2 varchar(255),
        city varchar(255),
        state_code varchar(255),
        post_code int(5),
        updated timestamp,
        PRIMARY KEY(contact_id));");
$create_contacts->execute();
$create_contacts->close();

/* Contacts */
$insert_contacts = $db_connection->prepare(
	"INSERT INTO Contacts
		(contact_id, last_name, first_name, email, phone, street_1, street_2, city, state_code, post_code, updated) VALUES(?,?,?,?,?,?,?,?,?,?,?);");
$insert_contacts->bind_param("issssssssis", $contact_id, $last_name, $first_name, $email, $phone, $street_1, $street_2, $city, $state_code, $post_code, $updated);
$contact_id = 1;
$last_name = "Doe";
$first_name = "John";
$email = "johndoe@example.com";
$phone = "123-456-7890";
$street_1 = "123 Main St";
$street_2 = "";
$city = "Anytown";
$state_code = "CA";
$post_code = 12345;
$updated = date("Y-m-d H:i:s");
$insert_contacts->execute();

$contact_id = 2;
$last_name = "Smith";
$first_name = "Jane";
$email = "janesmith@example.com";
$phone = "987-654-3210";
$street_1 = "456 Elm St";
$street_2 = "Apt 2";
$city = "Othertown";
$state_code = "NY";
$post_code = 54321;
$updated = date("Y-m-d H:i:s");
$insert_contacts->execute();

$contact_id = 3;
$last_name = "Brown";
$first_name = "Charlie";
$email = "charliebrown@example.com";
$phone = "555-555-5555";
$street_1 = "789 Oak St";
$street_2 = "";
$city = "Sometown";
$state_code = "TX";
$post_code = 67890;
$updated = date("Y-m-d H:i:s");
$insert_contacts->execute();

$contact_id = 4;
$last_name = "Password";
$first_name = "Admin";
$email = "adminpassword@example.com";
$phone = "555-123-4567";
$street_1 = "501 Crescent Street";
$street_2 = "";
$city = "Another town";
$state_code = "NH";
$post_code = 06515;
$updated = date("Y-m-d H:i:s");
$insert_contacts->execute();

$insert_contacts->close();
