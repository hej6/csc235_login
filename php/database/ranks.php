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
