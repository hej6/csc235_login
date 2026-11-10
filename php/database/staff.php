<?php
// Database initialization component: run through php/start.php only.
if (!defined('DB_SETUP_RUNNING')) {
    http_response_code(403);
    exit('Run php/start.php to initialize the database.');
}

$db_connection->query(
    "CREATE TABLE `Staff` (
        `staff_id` INT(11) NOT NULL AUTO_INCREMENT,
        `first_name` VARCHAR(50) NOT NULL,
        `last_name` VARCHAR(50) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `supervisor_id` INT(11) NULL,
        `job_id` INT(11) NOT NULL,
        `college_id` INT(11) NULL,
        `rank_id` INT(11) NOT NULL,
        `org_id` INT(11) NOT NULL,
        `dept_id` INT(11) NULL,
        PRIMARY KEY (`staff_id`),
        CONSTRAINT `fk_staff_supervisor` FOREIGN KEY (`supervisor_id`) REFERENCES `Staff` (`staff_id`),
        CONSTRAINT `fk_staff_job` FOREIGN KEY (`job_id`) REFERENCES `Job` (`job_id`),
        CONSTRAINT `fk_staff_college` FOREIGN KEY (`college_id`) REFERENCES `College` (`college_id`),
        CONSTRAINT `fk_staff_rank` FOREIGN KEY (`rank_id`) REFERENCES `Ranks` (`ranks_id`),
        CONSTRAINT `fk_staff_organization` FOREIGN KEY (`org_id`) REFERENCES `Organization` (`org_id`),
        CONSTRAINT `fk_staff_department` FOREIGN KEY (`dept_id`) REFERENCES `Department` (`dept_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

// Dummy records. Insert managers BEFORE their reports, so every non-null supervisor_id already exists
$seed_stmt = $db_connection->prepare(
    "INSERT INTO `Staff` (`staff_id`, `first_name`, `last_name`, `email`, `supervisor_id`, `job_id`, `college_id`, `rank_id`, `org_id`, `dept_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$seed_rows = [
    [1, 'Leroy', 'Jenkins', 'leroy.jenkins@example.com', null, 1, null, 1, 4, 1],
    [2, 'Jordan', 'Ellis', 'jordan.ellis@example.com', 1, 2, null, 1, 1, 2],
    [3, 'Taylor', 'Reed', 'taylor.reed@example.com', 1, 3, null, 2, 2, 3],
    [4, 'Morgan', 'Patel', 'morgan.patel@example.com', 2, 7, 1, 2, 1, 4],
    [5, 'Eugene', 'Krabs', 'eugene.krabs@example.com', 3, 4, null, 2, 2, 3],
    [6, 'Jamie', 'Brooks', 'jamie.brooks@example.com', 5, 5, null, 4, 2, 3],
    [7, 'Casey', 'Nguyen', 'casey.nguyen@example.com', 5, 6, null, 4, 2, 3],
    [8, 'Drew', 'Walker', 'drew.walker@example.com', 4, 8, 1, 5, 1, 4],
    [9, 'Harper', 'Kim', 'harper.kim@example.com', 2, 9, null, 3, 3, 5],
    [10, 'Quinn', 'Rivera', 'quinn.rivera@example.com', 9, 10, null, 4, 3, 5],
    [11, 'Emerson', 'Blake', 'emerson.blake@example.com', 1, 11, null, 2, 4, 6],
    [12, 'Alexis', 'Taylor', 'alexis.taylor@example.com', 11, 12, null, 6, 4, 6],
    [13, 'Samira', 'Ford', 'samira.ford@example.com', 2, 7, 2, 2, 1, 7],
    [14, 'Thomas', 'Juntao', 'thomas.juntao@example.com', 13, 8, 2, 5, 1, 7],
    [15, 'Barack', 'Moss', 'barack.moss@example.com', 2, 13, null, 2, 1, 8],
    [16, 'Reese', 'Carter', 'reese.carter@example.com', 15, 14, null, 4, 1, 8],
];
foreach ($seed_rows as $row) {
    $staff_id = $row[0];
    $first_name = $row[1];
    $last_name = $row[2];
    $email = $row[3];
    $supervisor_id = $row[4];
    $job_id = $row[5];
    $college_id = $row[6];
    $rank_id = $row[7];
    $org_id = $row[8];
    $dept_id = $row[9];
    $seed_stmt->bind_param("isssiiiiii", $staff_id, $first_name, $last_name, $email, $supervisor_id, $job_id, $college_id, $rank_id, $org_id, $dept_id);
    $seed_stmt->execute();
}
$seed_stmt->close();
