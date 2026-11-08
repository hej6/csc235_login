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

/* Below Are Tables That Have Foreign Keys */
/* ------------------------------------------ */
