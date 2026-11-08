<?php
// Database initialization component. Execute through php/start.php only.
if (!defined('DB_SETUP_RUNNING')) {
    http_response_code(403);
    exit('Run php/start.php to initialize the database.');
}

/*Classification*/
$create_classification = $db_connection->prepare(
	"CREATE OR REPLACE TABLE Classification(
        class_id int(11) NOT NULL AUTO_INCREMENT,
        class_name varchar(100) NOT NULL,
        PRIMARY KEY(class_id));");
$create_classification->execute();
$create_classification->close();
