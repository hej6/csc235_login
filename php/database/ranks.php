<?php
// Database initialization component. Execute through php/start.php only.
if (!defined('DB_SETUP_RUNNING')) {
    http_response_code(403);
    exit('Run php/start.php to initialize the database.');
}

/*Employee Rank*/
$create_ranks = $db_connection->prepare(
        "CREATE OR REPLACE TABLE Ranks(
        ranks_id int(11) NOT NULL AUTO_INCREMENT,
        ranks_title varchar(100) NOT NULL,
        min_salary decimal(8,2) NOT NULL,
        max_salary decimal(8,2) NOT NULL,
        class_id int(11) NOT NULL,
        FOREIGN KEY(class_id) REFERENCES Classification(class_id),
        PRIMARY KEY(ranks_id));");
$create_ranks->execute();
$create_ranks->close();

// Dummy records
$seed_stmt = $db_connection->prepare(
    "INSERT INTO `Ranks` (`ranks_id`, `ranks_title`, `min_salary`, `max_salary`, `class_id`) VALUES (?, ?, ?, ?, ?)"
);
$seed_rows = [
    [1, 'Executive Level', '120000.00', '200000.00', 1],
    [2, 'Director Level', '85000.00', '140000.00', 2],
    [3, 'Manager Level', '65000.00', '110000.00', 2],
    [4, 'Professional Level', '45000.00', '90000.00', 3],
    [5, 'Faculty Level', '55000.00', '120000.00', 4],
    [6, 'Support Level', '35000.00', '65000.00', 5],
];
foreach ($seed_rows as $row) {
    $ranks_id = $row[0];
    $ranks_title = $row[1];
    $min_salary = $row[2];
    $max_salary = $row[3];
    $class_id = $row[4];
    $seed_stmt->bind_param("isddi", $ranks_id, $ranks_title, $min_salary, $max_salary, $class_id);
    $seed_stmt->execute();
}
$seed_stmt->close();