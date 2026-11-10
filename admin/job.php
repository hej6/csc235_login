<?php
/**
 * Job management page.
 * Allows administrators to view, add, edit, and delete Jobs.
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
$job_title = '';
$edit_id = 0;

// Protect forms against requests submitted from another website.
if (empty($_SESSION['Job_token'])) {
    $_SESSION['Job_token'] = bin2hex(random_bytes(32));
}

function escapeJob($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$success = $_SESSION['Job_success'] ?? '';
unset($_SESSION['Job_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $action = $_POST['action'] ?? '';

    $Job_id = filter_var(
        $_POST['Job_id'] ?? '',
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    $job_title = is_string($_POST['job_title'] ?? null)
        ? trim($_POST['job_title'])
        : '';

    if ($action === 'update' && $Job_id !== false) {
        $edit_id = $Job_id;
    }

    if (!is_string($token) ||
        !hash_equals($_SESSION['Job_token'], $token)) {
        $error = 'Your form expired. Reload the page and try again.';
    } elseif (!in_array($action, ['add', 'update', 'delete'], true)) {
        $error = 'Invalid action.';
    } elseif ($action !== 'add' && $Job_id === false) {
        $error = 'Please select a valid Job.';
    } elseif (
        $action !== 'delete' &&
        ($job_title === '' || mb_strlen($job_title, 'UTF-8') > 255)
    ) {
        $error = 'Enter a Job title containing 1 to 255 characters.';
    } else {
        try {
            if ($action === 'add') {
                $stmt = $db_connection->prepare(
                    'INSERT INTO Job (job_title) VALUES (?)'
                );
                $stmt->bind_param('s', $job_title);
                $stmt->execute();
                $stmt->close();

                $_SESSION['Job_success'] = 'Job added.';
            } elseif ($action === 'update') {
                $stmt = $db_connection->prepare(
                    'UPDATE Job
                     SET job_title = ?
                     WHERE Job_id = ?'
                );
                $stmt->bind_param('si', $job_title, $Job_id);
                $stmt->execute();
                $stmt->close();

                $_SESSION['Job_success'] =
                    'Update completed. The list shows the current records.';
            } else {
                $stmt = $db_connection->prepare(
                    'DELETE FROM Job WHERE Job_id = ?'
                );
                $stmt->bind_param('i', $Job_id);
                $stmt->execute();
                $deleted = $stmt->affected_rows;
                $stmt->close();

                $_SESSION['Job_success'] = $deleted > 0
                    ? 'Job deleted.'
                    : 'That Job no longer exists.';
            }

            // Redirect so refreshing does not repeat a database change.
            header('Location: Job.php');
            exit;
        } catch (mysqli_sql_exception $e) {
            if ((int) $e->getCode() === 1451) {
                $error = 'This Job is used by another record and cannot be deleted.';
            } else {
                error_log($e->getMessage());
                $error = 'The change could not be saved. Please try again.';
            }
        }
    }
}

// Load the selected Job into the edit form.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit'])) {
    $requested_id = filter_var(
        $_GET['edit'],
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($requested_id === false) {
        $error = 'Invalid Job ID.';
    } else {
        $stmt = $db_connection->prepare(
            'SELECT Job_id, job_title
             FROM Job
             WHERE Job_id = ?'
        );
        $stmt->bind_param('i', $requested_id);
        $stmt->execute();
        $Job = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($Job) {
            $edit_id = (int) $Job['Job_id'];
            $job_title = $Job['job_title'];
        } else {
            $error = 'Job not found.';
        }
    }
}

$Jobs = $db_connection->query(
    'SELECT Job_id, job_title
     FROM Job
     ORDER BY job_title'
);
?>
<!doctype html>
<html lang="en">
<head>
    <?php require ROOT_PATH . '/include/head.php'; ?>
    <title>Manage Jobs</title>
</head>
<body class="<?php echo escapeJob($page_name); ?>">
<?php require ROOT_PATH . '/include/header.php'; ?>

<main class="container">
    <h1>Manage Jobs</h1>
    <p><a href="index.php">Back to dashboard</a></p>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success" role="status">
            <?php echo escapeJob($success); ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo escapeJob($error); ?>
        </div>
    <?php endif; ?>

    <section class="mb-4">
        <h2><?php echo $edit_id > 0 ? 'Edit Job' : 'Add Job'; ?></h2>

        <form method="post" action="Job.php">
            <input type="hidden" name="token"
                   value="<?php echo escapeJob($_SESSION['Job_token']); ?>">

            <input type="hidden" name="action"
                   value="<?php echo $edit_id > 0 ? 'update' : 'add'; ?>">

            <input type="hidden" name="Job_id"
                   value="<?php echo $edit_id; ?>">

            <div class="mb-3">
                <label for="job_title" class="form-label">
                    Job title
                </label>
                <input type="text"
                       class="form-control"
                       id="job_title"
                       name="job_title"
                       maxlength="255"
                       required
                       value="<?php echo escapeJob($job_title); ?>">
            </div>

            <button type="submit" class="btn btn-primary">
                <?php echo $edit_id > 0 ? 'Save Changes' : 'Add Job'; ?>
            </button>

            <?php if ($edit_id > 0): ?>
                <a href="Job.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </form>
    </section>

    <section>
        <h2>Existing Jobs</h2>

        <div class="table-responsive">
            <table class="table">
                <caption>Jobs available in the organizational chart system</caption>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Job title</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($Jobs->num_rows === 0): ?>
                    <tr>
                        <td colspan="3">No Jobs found.</td>
                    </tr>
                <?php else: ?>
                    <?php while ($row = $Jobs->fetch_assoc()): ?>
                        <tr>
                            <th scope="row">
                                <?php echo (int) $row['Job_id']; ?>
                            </th>
                            <td>
                                <?php echo escapeJob($row['job_title']); ?>
                            </td>
                            <td>
                                <a class="btn btn-sm btn-primary"
                                   href="Job.php?edit=<?php echo (int) $row['Job_id']; ?>"
                                   aria-label="Edit <?php echo escapeJob($row['job_title']); ?>">
                                    Edit
                                </a>

                                <form method="post"
                                      action="Job.php"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete this Job?');">
                                    <input type="hidden" name="token"
                                           value="<?php echo escapeJob($_SESSION['Job_token']); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="Job_id"
                                           value="<?php echo (int) $row['Job_id']; ?>">
                                    <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            aria-label="Delete <?php echo escapeJob($row['job_title']); ?>">
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