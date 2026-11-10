<?php
// Database initialization component. Execute through php/start.php only.
if (!defined('DB_SETUP_RUNNING')) {
    http_response_code(403);
    exit('Run php/start.php to initialize the database.');
}

/*Organization*/
$create_organization = $db_connection->prepare(
        "CREATE OR REPLACE TABLE Organization(
        org_id int(11) NOT NULL AUTO_INCREMENT,
        org_name varchar(50) NOT NULL,
        PRIMARY KEY(org_id));");
$create_organization->execute();
$create_organization->close();

// Dummy records
$seed_stmt = $db_connection->prepare(
    "INSERT INTO `Organization` (`org_id`, `org_name`) VALUES (?, ?)"
);
$seed_rows = [
    [1, 'Academic Affairs'],
    [2, 'Information Technology'],
    [3, 'Student Services'],
    [4, 'Administration'],
];
foreach ($seed_rows as $row) {
    $org_id = $row[0];
    $org_name = $row[1];
    $seed_stmt->bind_param("is", $org_id, $org_name);
    $seed_stmt->execute();
}
$seed_stmt->close();

