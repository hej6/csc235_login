<?php
/**
 * Building management page.
 * Allows administrators to view, add, edit, and delete buildings.
 */

require_once __DIR__ . '/../php/session.php';
require_once __DIR__ . '/../php/path.php';

// Stop unauthorized access before displaying or changing anything.
if (!isset($user_role) || (int) $user_role !== 1) {
    $_SESSION['message'] = 'Access Denied';
    header('Location: ' . SRC_PATH . '/logout.php');
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once ROOT_PATH . '/php/config.php';

$page_name = 'admin';
$error = '';
$building_name = '';
$edit_id = 0;

// Protect forms against requests submitted from another website.
if (empty($_SESSION['building_token'])) {
    $_SESSION['building_token'] = bin2hex(random_bytes(32));
}

function escapeBuilding($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$success = $_SESSION['building_success'] ?? '';
unset($_SESSION['building_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $action = $_POST['action'] ?? '';

    $building_id = filter_var(
        $_POST['building_id'] ?? '',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    $building_name = is_string($_POST['building_name'] ?? null)
        ? trim($_POST['building_name'])
        : '';

    if ($action === 'update' && $building_id !== false) {
        $edit_id = $building_id;
    }

    if (!is_string($token) ||
        !hash_equals($_SESSION['building_token'], $token)) {
        $error = 'Your form expired. Reload the page and try again.';
    } elseif (!in_array($action, ['add', 'update', 'delete'], true)) {
        $error = 'Invalid action.';
    } elseif ($action !== 'add' && $building_id === false) {
        $error = 'Please select a valid building.';
    } elseif (
        $action !== 'delete' &&
        ($building_name === '' || mb_strlen($building_name, 'UTF-8') > 100)
    ) {
        $error = 'Enter a building name containing 1 to 100 characters.';
    } else {
        try {
            if ($action === 'add') {
                $stmt = $db_connection->prepare(
                    'INSERT INTO Building (building_name) VALUES (?)'
                );
                $stmt->bind_param('s', $building_name);
                $stmt->execute();
                $stmt->close();

                $_SESSION['building_success'] = 'Building added.';
            } elseif ($action === 'update') {
                $stmt = $db_connection->prepare(
                    'UPDATE Building
                     SET building_name = ?
                     WHERE building_id = ?'
                );
                $stmt->bind_param('si', $building_name, $building_id);
                $stmt->execute();
                $stmt->close();

                $_SESSION['building_success'] =
                    'Update completed. The list shows the current records.';
            } else {
                $stmt = $db_connection->prepare(
                    'DELETE FROM Building WHERE building_id = ?'
                );
                $stmt->bind_param('i', $building_id);
                $stmt->execute();
                $deleted = $stmt->affected_rows;
                $stmt->close();

                $_SESSION['building_success'] = $deleted > 0
                    ? 'Building deleted.'
                    : 'That building no longer exists.';
            }

            // Redirect so refreshing does not repeat a database change.
            header('Location: building.php');
            exit;
        } catch (mysqli_sql_exception $e) {
            if ((int) $e->getCode() === 1451) {
                $error = 'This building is used by another record and cannot be deleted.';
            } else {
                error_log($e->getMessage());
                $error = 'The change could not be saved. Please try again.';
            }
        }
    }
}

// Load the selected building into the edit form.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit'])) {
    $requested_id = filter_var(
        $_GET['edit'],
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($requested_id === false) {
        $error = 'Invalid building ID.';
    } else {
        $stmt = $db_connection->prepare(
            'SELECT building_id, building_name
             FROM Building
             WHERE building_id = ?'
        );
        $stmt->bind_param('i', $requested_id);
        $stmt->execute();
        $building = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($building) {
            $edit_id = (int) $building['building_id'];
            $building_name = $building['building_name'];
        } else {
            $error = 'Building not found.';
        }
    }
}

$buildings = $db_connection->query(
    'SELECT building_id, building_name
     FROM Building
     ORDER BY building_name'
);
?>
<!doctype html>
<html lang="en">
<head>
    <?php require ROOT_PATH . '/include/head.php'; ?>
    <title>Manage Buildings</title>
</head>
<body class="<?php echo escapeBuilding($page_name); ?>">
<?php require ROOT_PATH . '/include/header.php'; ?>

<main class="container">
    <h1>Manage Buildings</h1>
    <p><a href="index.php">Back to dashboard</a></p>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success" role="status">
            <?php echo escapeBuilding($success); ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo escapeBuilding($error); ?>
        </div>
    <?php endif; ?>

    <section class="mb-4">
        <h2><?php echo $edit_id > 0 ? 'Edit Building' : 'Add Building'; ?></h2>

        <form method="post" action="building.php">
            <input type="hidden" name="token"
                   value="<?php echo escapeBuilding($_SESSION['building_token']); ?>">

            <input type="hidden" name="action"
                   value="<?php echo $edit_id > 0 ? 'update' : 'add'; ?>">

            <input type="hidden" name="building_id"
                   value="<?php echo $edit_id; ?>">

            <div class="mb-3">
                <label for="building_name" class="form-label">
                    Building name
                </label>
                <input type="text"
                       class="form-control"
                       id="building_name"
                       name="building_name"
                       maxlength="100"
                       required
                       value="<?php echo escapeBuilding($building_name); ?>">
            </div>

            <button type="submit" class="btn btn-primary">
                <?php echo $edit_id > 0 ? 'Save Changes' : 'Add Building'; ?>
            </button>

            <?php if ($edit_id > 0): ?>
                <a href="building.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </form>
    </section>

    <section>
        <h2>Existing Buildings</h2>

        <div class="table-responsive">
            <table class="table">
                <caption>Buildings available in the organizational chart system</caption>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Building name</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($buildings->num_rows === 0): ?>
                    <tr>
                        <td colspan="3">No buildings found.</td>
                    </tr>
                <?php else: ?>
                    <?php while ($row = $buildings->fetch_assoc()): ?>
                        <tr>
                            <th scope="row">
                                <?php echo (int) $row['building_id']; ?>
                            </th>
                            <td>
                                <?php echo escapeBuilding($row['building_name']); ?>
                            </td>
                            <td>
                                <a class="btn btn-sm btn-primary"
                                   href="building.php?edit=<?php echo (int) $row['building_id']; ?>"
                                   aria-label="Edit <?php echo escapeBuilding($row['building_name']); ?>">
                                    Edit
                                </a>

                                <form method="post"
                                      action="building.php"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete this building?');">
                                    <input type="hidden" name="token"
                                           value="<?php echo escapeBuilding($_SESSION['building_token']); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="building_id"
                                           value="<?php echo (int) $row['building_id']; ?>">
                                    <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            aria-label="Delete <?php echo escapeBuilding($row['building_name']); ?>">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php require ROOT_PATH . '/include/footer.php'; ?>
</body>
</html>