<?php
// Database initialization component: run through php/start.php only.
if (!defined('DB_SETUP_RUNNING')) {
    http_response_code(403);
    exit('Run php/start.php to initialize the database.');
}
$db_connection->query(
    "CREATE TABLE `Job` (
        `job_id` INT(11) NOT NULL AUTO_INCREMENT,
        `job_title` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`job_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

// Dummy records
$seed_stmt = $db_connection->prepare(
    "INSERT INTO `Job` (`job_id`, `job_title`) VALUES (?, ?)"
);
$seed_rows = [
    [1, 'University President'],
    [2, 'Provost'],
    [3, 'Chief Information Officer'],
    [4, 'IT Director'],
    [5, 'IT Support Specialist'],
    [6, 'Coach'],
    [7, 'Dean'],
    [8, 'Professor'],
    [9, 'Registrar'],
    [10, 'Academic Advisor'],
    [11, 'Human Resources Director'],
    [12, 'Administrative Specialist'],
    [13, 'Library Director'],
    [14, 'Librarian'],
    [15, 'Athletic Director'],
    [16, 'Head Coach'],
    [17, 'Athletic Trainer'],
    [18, 'Student Success Director'],
    [19, 'Career Services Director'],
    [20, 'Career Counselor'],
    [21, 'Department Chair']
];
foreach ($seed_rows as $row) {
    $job_id = $row[0];
    $job_title = $row[1];
    $seed_stmt->bind_param("is", $job_id, $job_title);
    $seed_stmt->execute();
}
$seed_stmt->close();
