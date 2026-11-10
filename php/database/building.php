<?php
// Database initialization component: run through php/start.php only.
if (!defined('DB_SETUP_RUNNING')) {
    http_response_code(403);
    exit('Run php/start.php to initialize the database.');
}

$db_connection->query(
    "CREATE TABLE `Building` (
        `building_id` INT(11) NOT NULL AUTO_INCREMENT,
        `building_name` VARCHAR(100) NOT NULL,
        PRIMARY KEY (`building_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

// Dummy records
$seed_stmt = $db_connection->prepare(
    "INSERT INTO `Building` (`building_id`, `building_name`) VALUES (?, ?)"
);
$seed_rows = [
    [1, 'Administration Building'],
    [2, 'Technology Center'],
    [3, 'Science Hall'],
    [4, 'Student Services Center'],
    [5, 'Business Hall'],
    [6, 'University Library'],
];
foreach ($seed_rows as $row) {
    $building_id = $row[0];
    $building_name = $row[1];
    $seed_stmt->bind_param("is", $building_id, $building_name);
    $seed_stmt->execute();
}
$seed_stmt->close();
