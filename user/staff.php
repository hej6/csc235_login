<?php
/**
 * User Index Page
 *
 * @author    Jackie Isabella <connorj4@southernct.edu>
 * @copyright 2026 
 * @date      2026-02-24
 * @version   1.0
 */
error_reporting(E_ALL); // Report all errors
ini_set('display_errors', 0); // set to 1 to display errors, 0 to hide them

  /* Quick Paths */
  /* note the 2 after __FILE__, because it's 2 directories deep */
  include_once (realpath(dirname(__FILE__, 2).'/php/session.php')); // Session will be included in header.php
  include_once (realpath(dirname(__FILE__, 2).'/php/path.php')); // Path will be included in header.php
  // Session will be included in header.php


$page_name = "Staff Directory"; // Set page name for active link in header

include_once (ROOT_PATH . '/php/config.php');

// ---- Dropdown lists (one SQL query per entity) ----
function lookup($db, $sql) {
  return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
}
$filters = [
  'org_id'      => ['label' => 'Division',       'list' => lookup($db_connection, "SELECT org_id AS id, org_name AS name FROM Organization ORDER BY org_name")],
  'college_id'  => ['label' => 'College',        'list' => lookup($db_connection, "SELECT college_id AS id, college_name AS name FROM College ORDER BY college_name")],
  'dept_id'     => ['label' => 'Department',     'list' => lookup($db_connection, "SELECT dept_id AS id, dept_name AS name FROM Department ORDER BY dept_name")],
  'building_id' => ['label' => 'Building',       'list' => lookup($db_connection, "SELECT building_id AS id, building_name AS name FROM Building ORDER BY building_name")],
  'class_id'    => ['label' => 'Classification', 'list' => lookup($db_connection, "SELECT class_id AS id, class_name AS name FROM Classification ORDER BY class_id")],
  'ranks_id'    => ['label' => 'Rank',           'list' => lookup($db_connection, "SELECT ranks_id AS id, ranks_title AS name FROM Ranks ORDER BY ranks_id")],
  'job_id'      => ['label' => 'Job',            'list' => lookup($db_connection, "SELECT job_id AS id, job_title AS name FROM Job ORDER BY job_title")],
];

// ---- Build the WHERE clause from search + filters ----
$where  = [];
$types  = '';
$params = [];

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
  $where[] = "(first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?
               OR email LIKE ? OR job_title LIKE ? OR class_name LIKE ? OR ranks_title LIKE ?
               OR org_name LIKE ? OR dept_name LIKE ? OR college_name LIKE ? OR building_name LIKE ?)";
  for ($i = 0; $i < 11; $i++) { $types .= 's'; $params[] = "%$q%"; }
}

foreach ($filters as $col => $f) {      // $col comes from our own list, never from the URL
  if (!empty($_GET[$col])) {
    $where[]  = "$col = ?";
    $types   .= 'i';
    $params[] = (int) $_GET[$col];
  }
}

// ---- Sorting (only allowed columns) ----
$sortable = [
  'last_name'     => ['Name',           'last_name, first_name'],
  'job_title'     => ['Job',            'job_title'],
  'class_name'    => ['Classification', 'class_id'],
  'ranks_title'   => ['Rank',           'ranks_id'],
  'org_name'      => ['Division',       'org_name'],
  'dept_name'     => ['Department',     'dept_name'],
  'college_name'  => ['College',        'college_name'],
  'building_name' => ['Building',       'building_name'],
];
$sort = array_key_exists($_GET['sort'] ?? '', $sortable) ? $_GET['sort'] : 'last_name';
$dir  = (($_GET['dir'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';

// Header link that keeps the current search and filters
function sortLink($col, $label, $sort, $dir) {
  $newDir = ($sort === $col && $dir === 'ASC') ? 'desc' : 'asc';
  $query  = http_build_query(array_merge($_GET, ['sort' => $col, 'dir' => $newDir]));
  $arrow  = ($sort === $col) ? ($dir === 'ASC' ? ' ▲' : ' ▼') : '';
  return '<a class="text-white text-decoration-none" href="?' . htmlspecialchars($query) . '">' . $label . $arrow . '</a>';
}

// ---- Run the query ----///////////////////
$sql = "SELECT * FROM vw_staff_directory"
     . ($where ? " WHERE " . implode(" AND ", $where) : "")
     . " ORDER BY " . $sortable[$sort][1] . " $dir";
$stmt = $db_connection->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$result = $stmt->get_result();
?>
<!doctype html>
<html lang="en">
  <head>
  <?php include_once (ROOT_PATH . '/include/head.php'); // Include head.php ?>
  </head>
  <body class="<?php echo $page_name; ?>">

  <?php include_once (ROOT_PATH . '/include/header.php'); // Include header.php ?>
    <main role="main" class="container">

      <h1 class="mb-3">Staff Directory</h1>

      <!-- Search and filters -->
      <form method="get" class="card card-body shadow-sm mb-4">
        <div class="row g-2">
          <div class="col-12">
            <input type="text" name="q" class="form-control" placeholder="Search name, email, job, department, building..." value="<?php echo htmlspecialchars($q); ?>">
          </div>
          <?php foreach ($filters as $col => $f): ?>
            <div class="col-md-3">
              <select name="<?php echo $col; ?>" class="form-select">
                <option value="">All <?php echo $f['label']; ?>s</option>
                <?php foreach ($f['list'] as $opt): ?>
                  <option value="<?php echo $opt['id']; ?>" <?php echo (($_GET[$col] ?? '') == $opt['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($opt['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endforeach; ?>
          <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary">Apply</button>
            <a href="staff.php" class="btn btn-outline-secondary">Reset</a>
          </div>
        </div>
      </form>

      <p class="text-muted"><?php echo $result->num_rows; ?> staff found</p>

      <!-- Staff table -->
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <?php foreach ($sortable as $col => $s): ?>
                <th><?php echo sortLink($col, $s[0], $sort, $dir); ?></th>
              <?php endforeach; ?>
              <th>Supervisor</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . htmlspecialchars($row['last_name'] . ', ' . $row['first_name']) . "<br><small class='text-muted'>" . htmlspecialchars($row['email']) . "</small></td>";
                  echo "<td>" . htmlspecialchars($row['job_title']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['class_name']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['ranks_title']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['org_name']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['dept_name'] ?? 'N/A') . "</td>";
                  echo "<td>" . htmlspecialchars($row['college_name'] ?? 'N/A') . "</td>";
                  echo "<td>" . htmlspecialchars($row['building_name'] ?? 'N/A') . "</td>";
                  echo "<td>" . htmlspecialchars($row['supervisor_name'] ?? 'None') . "</td>";
                  echo "</tr>";
                }
              } else {
                echo "<tr><td colspan='9' class='text-center'>No staff match your search.</td></tr>";
              }
              $stmt->close();
              $db_connection->close();
            ?>
          </tbody>
        </table>
      </div>

    </main>
    <?php include_once (ROOT_PATH . '/include/footer.php'); ?>
  </body>
</html>