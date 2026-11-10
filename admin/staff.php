<?php
require_once __DIR__ . '/../php/session.php';
require_once __DIR__ . '/../php/path.php';

if ((int) ($user_role ?? 0) !== 1) {
    $_SESSION['message'] = 'Access Denied';
    header('Location: ' . SRC_PATH . '/logout.php');
    exit;
}

require_once ROOT_PATH . '/php/config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$page_name = 'admin';
$error = '';
$success = $_SESSION['staff_success'] ?? '';
unset($_SESSION['staff_success']);
$_SESSION['staff_token'] ??= bin2hex(random_bytes(32));

function staffEscape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function staffId($value)
{
    return filter_var(
        $value,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
}

$fields = [
    'job_id' => [
        'Job', true,
        'SELECT job_id AS id, job_title AS label FROM Job ORDER BY job_title'
    ],
    'rank_id' => [
        'Rank', true,
        'SELECT ranks_id AS id, ranks_title AS label FROM Ranks ORDER BY ranks_title'
    ],
    'org_id' => [
        'Organization', true,
        'SELECT org_id AS id, org_name AS label FROM Organization ORDER BY org_name'
    ],
    'supervisor_id' => [
        'Supervisor', false,
        "SELECT staff_id AS id, CONCAT(first_name, ' ', last_name) AS label
         FROM Staff ORDER BY last_name, first_name"
    ],
    'college_id' => [
        'College', false,
        'SELECT college_id AS id, college_name AS label FROM College ORDER BY college_name'
    ],
    'dept_id' => [
        'Department', false,
        'SELECT dept_id AS id, dept_name AS label FROM Department ORDER BY dept_name'
    ],
];

$options = [];
foreach ($fields as $key => [$label, $required, $sql]) {
    $options[$key] = [];
    foreach ($db_connection->query($sql) as $row) {
        $options[$key][(int) $row['id']] = $row['label'];
    }
}

$staff = [];
foreach ($db_connection->query(
    'SELECT * FROM Staff ORDER BY last_name, first_name, staff_id'
) as $row) {
    $staff[(int) $row['staff_id']] = $row;
}

$editId = 0;
$form = array_fill_keys(
    array_merge(['first_name', 'last_name', 'email'], array_keys($fields)),
    ''
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['token'] ?? '';
    $id = staffId($_POST['staff_id'] ?? '');

    try {
        if (!is_string($token) || !hash_equals($_SESSION['staff_token'], $token)) {
            throw new InvalidArgumentException('Reload the page and try again.');
        }

        if (!in_array($action, ['add', 'update', 'delete'], true)) {
            throw new InvalidArgumentException('Invalid staff action.');
        }

        if ($action !== 'add' && ($id === false || !isset($staff[$id]))) {
            throw new InvalidArgumentException('That staff record no longer exists.');
        }

        if ($action === 'delete') {
            $stmt = $db_connection->prepare('DELETE FROM Staff WHERE staff_id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $_SESSION['staff_success'] = $stmt->affected_rows
                ? 'Staff member deleted.'
                : 'That staff record no longer exists.';
        } else {
            $editId = $action === 'update' ? $id : 0;

            foreach (array_keys($form) as $key) {
                $value = $_POST[$key] ?? '';
                $form[$key] = is_string($value) ? trim($value) : '';
            }

            $first = $form['first_name'];
            $last = $form['last_name'];
            $email = $form['email'];

            if ($first === '' || $last === '' ||
                mb_strlen($first, 'UTF-8') > 50 ||
                mb_strlen($last, 'UTF-8') > 50) {
                throw new InvalidArgumentException(
                    'Enter a first and last name of up to 50 characters each.'
                );
            }

            if (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Enter a valid email address.');
            }

            $ids = [];
            foreach ($fields as $key => [$label, $required]) {
                if (!$required && $form[$key] === '') {
                    $ids[$key] = null;
                    continue;
                }

                $selected = staffId($form[$key]);
                if ($selected === false || !isset($options[$key][$selected])) {
                    throw new InvalidArgumentException("Choose a valid option for $label.");
                }

                $ids[$key] = $selected;
            }

            $supervisor = $ids['supervisor_id'];
            $job = $ids['job_id'];
            $college = $ids['college_id'];
            $rank = $ids['rank_id'];
            $org = $ids['org_id'];
            $department = $ids['dept_id'];

            // Follow the reporting chain to prevent a circular assignment.
            $visited = [];
            for ($current = $supervisor; $current !== null;
                 $current = $staff[$current]['supervisor_id'] ?? null) {
                $current = (int) $current;
                if ($current === $editId || isset($visited[$current])) {
                    throw new InvalidArgumentException(
                        'Choose a supervisor whose reporting chain does not loop back.'
                    );
                }
                $visited[$current] = true;
            }

            if ($action === 'add') {
                $stmt = $db_connection->prepare(
                    'INSERT INTO Staff
                     (first_name, last_name, email, supervisor_id, job_id,
                      college_id, rank_id, org_id, dept_id)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param(
                    'sssiiiiii', $first, $last, $email, $supervisor,
                    $job, $college, $rank, $org, $department
                );
            } else {
                $stmt = $db_connection->prepare(
                    'UPDATE Staff SET first_name = ?, last_name = ?, email = ?,
                     supervisor_id = ?, job_id = ?, college_id = ?, rank_id = ?,
                     org_id = ?, dept_id = ? WHERE staff_id = ?'
                );
                $stmt->bind_param(
                    'sssiiiiiii', $first, $last, $email, $supervisor,
                    $job, $college, $rank, $org, $department, $id
                );
            }

            $stmt->execute();
            $_SESSION['staff_success'] = $action === 'add'
                ? 'Staff member added.'
                : 'Staff update completed.';
        }

        $stmt->close();
        header('Location: staff.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (mysqli_sql_exception $e) {
        if ((int) $e->getCode() === 1451) {
            $error = 'This staff member is still referenced by another record. Reassign their direct reports before deleting them.';
        } elseif ((int) $e->getCode() === 1452) {
            $error = 'A selected record is no longer available. Reload and try again.';
        } else {
            error_log($e->getMessage());
            $error = 'The staff record could not be saved.';
        }
    }
} elseif (isset($_GET['edit'])) {
    $id = staffId($_GET['edit']);
    if ($id !== false && isset($staff[$id])) {
        $editId = $id;
        $form = array_replace($form, array_intersect_key($staff[$id], $form));
    } else {
        $error = 'Staff member not found.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <?php require_once ROOT_PATH . '/include/head.php'; ?>
</head>
<body class="admin">
<?php require_once ROOT_PATH . '/include/header.php'; ?>

<main class="container">
    <h1>Manage Staff</h1>
    <p><a href="index.php">Back to dashboard</a></p>

    <?php if ($success !== ''): ?>
        <p class="alert alert-success" role="status"><?= staffEscape($success) ?></p>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <p class="alert alert-danger" role="alert"><?= staffEscape($error) ?></p>
    <?php endif; ?>

    <h2><?= $editId ? 'Edit Staff Member' : 'Add Staff Member' ?></h2>
    <form method="post" action="staff.php" class="mb-4">
        <input type="hidden" name="token" value="<?= staffEscape($_SESSION['staff_token']) ?>">
        <input type="hidden" name="action" value="<?= $editId ? 'update' : 'add' ?>">
        <input type="hidden" name="staff_id" value="<?= $editId ?>">

        <?php foreach ([
            'first_name' => ['First name', 'text', 50],
            'last_name' => ['Last name', 'text', 50],
            'email' => ['Email', 'email', 255],
        ] as $key => [$label, $type, $limit]): ?>
            <div class="mb-3">
                <label class="form-label" for="<?= $key ?>"><?= $label ?></label>
                <input class="form-control" id="<?= $key ?>" name="<?= $key ?>"
                       type="<?= $type ?>" maxlength="<?= $limit ?>"
                       value="<?= staffEscape($form[$key]) ?>" required>
            </div>
        <?php endforeach; ?>

        <?php foreach ($fields as $key => [$label, $required]): ?>
            <div class="mb-3">
                <label class="form-label" for="<?= $key ?>">
                    <?= $label ?><?= $required ? '' : ' (optional)' ?>
                </label>
                <select class="form-select" id="<?= $key ?>" name="<?= $key ?>"
                        <?= $required ? 'required' : '' ?>>
                    <option value=""><?= $required ? 'Select an option' : 'None' ?></option>
                    <?php foreach ($options[$key] as $value => $text): ?>
                        <?php if ($key === 'supervisor_id' && $value === $editId) continue; ?>
                        <option value="<?= $value ?>"
                            <?= (string) $form[$key] === (string) $value ? 'selected' : '' ?>>
                            <?= staffEscape($text) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>

        <button class="btn btn-primary" type="submit">
            <?= $editId ? 'Save Changes' : 'Add Staff Member' ?>
        </button>
        <?php if ($editId): ?>
            <a href="staff.php">Cancel</a>
        <?php endif; ?>
    </form>

    <h2>Existing Staff</h2>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <?php foreach ($fields as [$label]): ?>
                        <th scope="col"><?= $label ?></th>
                    <?php endforeach; ?>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$staff): ?>
                <tr><td colspan="9">No staff members found.</td></tr>
            <?php endif; ?>
            <?php foreach ($staff as $id => $row): ?>
                <?php $name = $row['first_name'] . ' ' . $row['last_name']; ?>
                <tr>
                    <th scope="row"><?= staffEscape($name) ?></th>
                    <td><?= staffEscape($row['email']) ?></td>
                    <?php foreach ($fields as $key => $field): ?>
                        <td><?= staffEscape($options[$key][$row[$key]] ?? 'None') ?></td>
                    <?php endforeach; ?>
                    <td>
                        <a href="staff.php?edit=<?= $id ?>"
                           aria-label="Edit <?= staffEscape($name) ?>">Edit</a>
                        <form method="post" action="staff.php" class="d-inline"
                              onsubmit="return confirm('Delete this staff member?');">
                            <input type="hidden" name="token"
                                   value="<?= staffEscape($_SESSION['staff_token']) ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="staff_id" value="<?= $id ?>">
                            <button type="submit" class="btn btn-sm btn-danger"
                                    aria-label="Delete <?= staffEscape($name) ?>">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once ROOT_PATH . '/include/footer.php'; ?>
</body>
</html>