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
    // Office of the President (dept 1)
    [1, 'Leroy', 'Jenkins', 'leroy.jenkins@example.com', null, 1, null, 1, 4, 1],
    // Office of the Provost (dept 2)
    [2, 'Tifa', 'Lockhart', 'tifa.lockhart@example.com', 1, 2, null, 1, 1, 2],
    // Information Technology (dept 3)
    [3, 'Gordon', 'Freeman', 'gordon.freeman@example.com', 1, 3, null, 2, 2, 3],
    [5, 'Samus', 'Aran', 'samus.aran@example.com', 3, 4, null, 2, 2, 3],
    [6, 'Claptrap', 'Hyperion', 'claptrap.hyperion@example.com', 5, 5, null, 4, 2, 3],
    [7, 'Tracer', 'Oxton', 'tracer.oxton@example.com', 5, 5, null, 4, 2, 3],
    [29, 'Tom', 'Nook', 'tom.nook@example.com', 5, 5, null, 4, 2, 3],
    // Computer Science (dept 4)
    [4, 'Aerith', 'Gainsborough', 'aerith.gainsborough@example.com', 2, 7, 1, 2, 1, 4],
    [8, 'Geralt', 'Rivia', 'geralt.rivia@example.com', 4, 8, 1, 5, 1, 4],
    [24, 'Yennefer', 'Vengerberg', 'yennefer.vengerberg@example.com', 4, 8, 1, 5, 1, 4],
    [25, 'Triss', 'Merigold', 'triss.merigold@example.com', 4, 8, 1, 5, 1, 4],
    [26, 'Zoltan', 'Chivay', 'zoltan.chivay@example.com', 4, 12, 1, 6, 1, 4],
    // Registrar (dept 5)
    [9, 'Lara', 'Croft', 'lara.croft@example.com', 2, 9, null, 3, 3, 5],
    [10, 'Ezio', 'Auditore', 'ezio.auditore@example.com', 9, 10, null, 4, 3, 5],
    // Human Resources (dept 6)
    [11, 'Sarah', 'Kerrigan', 'sarah.kerrigan@example.com', 1, 11, null, 2, 4, 6],
    [12, 'Jim', 'Raynor', 'jim.raynor@example.com', 11, 12, null, 6, 4, 6],
    [30, 'Tychus', 'Findlay', 'tychus.findlay@example.com', 11, 12, null, 6, 4, 6],
    // Business Administration (dept 7)
    [13, 'Sid', 'Meier', 'sid.meier@example.com', 2, 7, 2, 2, 1, 7],
    [14, 'Wario', 'Ware', 'wario.ware@example.com', 13, 8, 2, 5, 1, 7],
    [27, 'Lucca', 'Ashtear', 'lucca.ashtear@example.com', 13, 8, 2, 5, 1, 7],
    [28, 'Crono', 'Truce', 'crono.truce@example.com', 13, 8, 2, 5, 1, 7],
    // Student Success / Library (dept 8)
    [15, 'Deckard', 'Cain', 'deckard.cain@example.com', 2, 13, null, 2, 1, 8],
    [16, 'Alyx', 'Vance', 'alyx.vance@example.com', 15, 14, null, 4, 1, 8],
    [31, 'Marsha', 'Johnson', 'marsha.johnson@example.com', 1, 18, null, 3, 3, 8],
    [32, 'Harvey', 'Milk', 'harvey.milk@example.com', 31, 10, null, 4, 3, 8],
    [33, 'Dolores', 'Huerta', 'dolores.huerta@example.com', 31, 10, null, 4, 3, 8],
    // Career Center (dept 9)
    [34, 'Malala', 'Yousafzai', 'malala.yousafzai@example.com', 1, 19, null, 3, 3, 9],
    [35, 'Fannie', 'Hamer', 'fannie.hamer@example.com', 34, 20, null, 4, 3, 9],
    // Athletics (dept 10)
    [17, 'Billie Jean', 'King', 'billiejean.king@example.com', 1, 15, null, 2, 5, 10],
    [18, 'Ryu', 'Hoshi', 'ryu.hoshi@example.com', 17, 16, null, 3, 5, 10],
    [19, 'Ken', 'Masters', 'ken.masters@example.com', 17, 16, null, 3, 5, 10],
    [20, 'Little', 'Mac', 'little.mac@example.com', 18, 6, null, 4, 5, 10],
    [21, 'Chun-Li', 'Zhang', 'chunli.zhang@example.com', 19, 6, null, 4, 5, 10],
    [22, 'Mercy', 'Ziegler', 'mercy.ziegler@example.com', 17, 17, null, 4, 5, 10],
    [23, 'Toadette', 'Kinopio', 'toadette.kinopio@example.com', 17, 12, null, 6, 5, 10],
    // Fine Arts (dept 11)
    [38, 'Frida', 'Kahlo', 'frida.kahlo@example.com', 4, 21, 1, 3, 1, 11],
    [39, 'Georgia', 'O\'Keeffe', 'georgia.okeeffe@example.com', 38, 8, 1, 5, 1, 11],
    [40, 'Claude', 'Monet', 'claude.monet@example.com', 38, 8, 1, 5, 1, 11],
    [72, 'Peppermint', 'Butler', 'peppermint.butler@example.com', 38, 12, 1, 6, 1, 11],
    // English (dept 12)
    [41, 'Marceline', 'Abadeer', 'marceline.abadeer@example.com', 4, 21, 1, 3, 1, 12],
    [42, 'Simon', 'Petrikov', 'simon.petrikov@example.com', 41, 8, 1, 5, 1, 12],
    [43, 'Bonnibel', 'Bubblegum', 'bonnibel.bubblegum@example.com', 41, 8, 1, 5, 1, 12],
    // History (dept 13)
    [44, 'Link', 'Hero', 'link.hero@example.com', 4, 21, 1, 3, 1, 13],
    [45, 'Zelda', 'Hyrule', 'zelda.hyrule@example.com', 44, 8, 1, 5, 1, 13],
    [46, 'Ganondorf', 'Dragmire', 'ganondorf.dragmire@example.com', 44, 8, 1, 5, 1, 13],
    // Mathematics (dept 14)
    [47, 'Andrew', 'Carnegie', 'andrew.carnegie@example.com', 4, 21, 1, 3, 1, 14],
    [48, 'Wirt', 'Pottsfield', 'wirt.pottsfield@example.com', 47, 8, 1, 5, 1, 14],
    [49, 'Greg', 'Tadpole', 'greg.tadpole@example.com', 47, 8, 1, 5, 1, 14],
    // Biology (dept 15)
    [50, 'Kermit', 'Frog', 'kermit.frog@example.com', 4, 21, 1, 3, 1, 15],
    [51, 'Fozzie', 'Bear', 'fozzie.bear@example.com', 50, 8, 1, 5, 1, 15],
    [52, 'Bunsen', 'Honeydew', 'bunsen.honeydew@example.com', 50, 8, 1, 5, 1, 15],
    // Chemistry (dept 16)
    [53, 'Sam', 'Eagle', 'sam.eagle@example.com', 4, 21, 1, 3, 1, 16],
    [54, 'Miss', 'Piggy', 'miss.piggy@example.com', 53, 8, 1, 5, 1, 16],
    [55, 'Rowlf', 'Dog', 'rowlf.dog@example.com', 53, 8, 1, 5, 1, 16],
    // Physics (dept 17)
    [56, 'Yayoi', 'Kusama', 'yayoi.kusama@example.com', 4, 21, 1, 3, 1, 17],
    [57, 'Pablo', 'Picasso', 'pablo.picasso@example.com', 56, 8, 1, 5, 1, 17],
    [58, 'Salvador', 'Dali', 'salvador.dali@example.com', 56, 8, 1, 5, 1, 17],
    // Psychology (dept 18)
    [59, 'Bob', 'Ross', 'bob.ross@example.com', 4, 21, 1, 3, 1, 18],
    [60, 'Andy', 'Warhol', 'andy.warhol@example.com', 59, 8, 1, 5, 1, 18],
    [61, 'Mary', 'Cassatt', 'mary.cassatt@example.com', 59, 8, 1, 5, 1, 18],
    // Music (dept 19)
    [62, 'Keith', 'Haring', 'keith.haring@example.com', 4, 21, 1, 3, 1, 19],
    [63, 'Jean-Michel', 'Basquiat', 'jeanmichel.basquiat@example.com', 62, 8, 1, 5, 1, 19],
    [64, 'Artemisia', 'Gentileschi', 'artemisia.gentileschi@example.com', 62, 8, 1, 5, 1, 19],
    // Theatre (dept 20)
    [65, 'Leonardo', 'da Vinci', 'leonardo.davinci@example.com', 4, 21, 1, 3, 1, 20],
    [66, 'Melinda', 'Gates', 'melinda.gates@example.com', 65, 8, 1, 5, 1, 20],
    [67, 'Mackenzie', 'Scott', 'mackenzie.scott@example.com', 65, 8, 1, 5, 1, 20],
    // Nursing (dept 21)
    [37, 'Dolly', 'Parton', 'dolly.parton@example.com', 2, 7, 4, 2, 1, 21],
    [68, 'Finn', 'Mertens', 'finn.mertens@example.com', 37, 8, 4, 5, 1, 21],
    [69, 'Beatrice', 'Bluebird', 'beatrice.bluebird@example.com', 37, 8, 4, 5, 1, 21],
    [73, 'Camilla', 'Chicken', 'camilla.chicken@example.com', 37, 12, 4, 6, 1, 21],
    // Education (dept 22)
    [36, 'Fred', 'Rogers', 'fred.rogers@example.com', 2, 7, 3, 2, 1, 22],
    [70, 'Paul', 'Newman', 'paul.newman@example.com', 36, 8, 3, 5, 1, 22],
    [71, 'Rizzo', 'Rat', 'rizzo.rat@example.com', 36, 8, 3, 5, 1, 22],
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
