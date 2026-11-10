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

// Dummy records
$seed_stmt = $db_connection->prepare(
    "INSERT INTO `Classification` (`class_id`, `class_name`) VALUES (?, ?)"
);
$seed_rows = [
    [1, 'Executive'],
    [2, 'Management'],
    [3, 'Professional'],
    [4, 'Faculty'],
    [5, 'Support'],
];
foreach ($seed_rows as $row) {
    $class_id = $row[0];
    $class_name = $row[1];
    $seed_stmt->bind_param("is", $class_id, $class_name);
    $seed_stmt->execute();
}
$seed_stmt->close();