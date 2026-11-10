<?php
// Database initialization component: run through php/start.php only.
if (!defined('DB_SETUP_RUNNING')) {
    http_response_code(403);
    exit('Run php/start.php to initialize the database.');
}

$db_connection->query(
    "CREATE TABLE `College` (
        `college_id` INT(11) NOT NULL AUTO_INCREMENT,
        `college_name` VARCHAR(50) NOT NULL,
        PRIMARY KEY (`college_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

// Dummy records
$seed_stmt = $db_connection->prepare(
    "INSERT INTO `College` (`college_id`, `college_name`) VALUES (?, ?)"
);
$seed_rows = [
    [1, 'College of Arts and Sciences'],
    [2, 'School of Business'],
    [3, 'School of Education'],
    [4, 'School of Health and Human Services'],
];
foreach ($seed_rows as $row) {
    $college_id = $row[0];
    $college_name = $row[1];
    $seed_stmt->bind_param("is", $college_id, $college_name);
    $seed_stmt->execute();
}
$seed_stmt->close();
