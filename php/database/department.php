<?php
// Database initialization component: run through php/start.php only.
if (!defined('DB_SETUP_RUNNING')) {
    http_response_code(403);
    exit('Run php/start.php to initialize the database.');
}

$db_connection->query(
    "CREATE TABLE `Department` (
        `dept_id` INT(11) NOT NULL AUTO_INCREMENT,
        `dept_name` VARCHAR(255) NOT NULL,
        `org_id` INT(11) NOT NULL,
        `building_id` INT(11) NULL,
        PRIMARY KEY (`dept_id`),
        CONSTRAINT `fk_department_organization` FOREIGN KEY (`org_id`) REFERENCES `Organization` (`org_id`),
        CONSTRAINT `fk_department_building` FOREIGN KEY (`building_id`) REFERENCES `Building` (`building_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

// Dummy records
$seed_stmt = $db_connection->prepare(
    "INSERT INTO `Department` (`dept_id`, `dept_name`, `org_id`, `building_id`) VALUES (?, ?, ?, ?)"
);
$seed_rows = [
    [1, 'Office of the President', 4, 1],
    [2, 'Office of the Provost', 1, 1],
    [3, 'Information Technology', 2, 2],
    [4, 'Computer Science', 1, 3],
    [5, 'Registrar',4, 1],
    [6, 'Human Resources', 4, 1],
    [7, 'Business Administration', 1, 1],
    [8, 'Student Success', 3, 4],
    [9, 'Career Center', 3, 6],
    [10, 'Athletics', 5, 5],
    [11, 'Fine Arts', 1, 7],
    [12, 'English', 1, 3],
    [13, 'History', 1, 3],
    [14, 'Mathematics', 1, 3],
    [15, 'Biology', 1, 2],
    [16, 'Chemistry', 1, 2],
    [17, 'Physics', 1, 2],
    [18, 'Psychology', 1, 3],
    [19, 'Music', 1, 7],
    [20, 'Theatre', 1, 7],
    [21, 'Nursing', 1, 3],
    [22, 'Education', 1, 3],

];
foreach ($seed_rows as $row) {
    $dept_id = $row[0];
    $dept_name = $row[1];
    $org_id = $row[2];
    $building_id = $row[3];
    $seed_stmt->bind_param("isii", $dept_id, $dept_name, $org_id, $building_id);
    $seed_stmt->execute();
}
$seed_stmt->close();
