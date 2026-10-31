
<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: Login.html');
    exit;
}

if (($_SESSION['student_role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied. Only administrators can manage students.');
}

require_once __DIR__ . '/backend/db.php';

// Display feedback after an operation.
$message = $_SESSION['student_message'] ?? '';
$messageType = $_SESSION['student_message_type'] ?? 'success';

unset($_SESSION['student_message'], $_SESSION['student_message_type']);

// Read search and filter values.
$search = trim($_GET['search'] ?? '');
$department = trim($_GET['department'] ?? '');
$semester = trim($_GET['semester'] ?? '');

// Fetch student records using prepared statements.
$sql = "SELECT id, name, username, email, enrollment,
               phone, department, semester, role
        FROM students
        WHERE role = 'student'";

$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND (
        name LIKE ?
        OR username LIKE ?
        OR email LIKE ?
        OR enrollment LIKE ?
    )";

    $searchTerm = '%' . $search . '%';

    $params = array_merge($params, [
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm
    ]);

    $types .= 'ssss';
}

if ($department !== '') {
    $sql .= " AND department = ?";
    $params[] = $department;
    $types .= 's';
}

if ($semester !== '') {
    $sql .= " AND semester = ?";
    $params[] = (int) $semester;
    $types .= 'i';
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$students = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();

function e($value) {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Management | StudentHub</title>

    <link rel="stylesheet" href="Style.css">

    <style>
        .management-container {
            width: 95%;
            max-width: 1400px;
            margin: 25px auto;
        }

        .management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .management-header h1 {
            color: #321E48;
            margin: 0;
        }

        .management-card {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 22px;
            margin-bottom: 22px;
            box-shadow: 0 3px 12px rgba(50, 30, 72, 0.10);
            overflow-x: auto;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: end;
        }

        .filter-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1 1 170px;
        }

        .filter-field label {
            font-weight: 600;
            color: #321E48;
        }

        .filter-field input,
        .filter-field select {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            border: 1px solid #D5C9DF;
            border-radius: 6px;
            background: #FFFFFF;
        }

        .management-btn {
            display: inline-block;
            border: none;
            border-radius: 6px;
            padding: 10px 15px;
            background: #321E48;
            color: #FFFFFF;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .management-btn:hover {
            background: #5B3B7A;
        }

        .secondary-btn {
            background: #EDE6F3;
            color: #321E48;
        }

        .danger-btn {
            background: #A52A3A;
        }

        .danger-btn:hover {
            background: #801E2C;
        }

        .message-box {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 18px;
        }

        .message-success {
            background: #E4F5E9;
            color: #216638;
        }

        .message-error {
            background: #FBE8E8;
            color: #922B35;
        }

        .student-table {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .student-table th,
        .student-table td {
            padding: 13px 12px;
            text-align: left;
            border-bottom: 1px solid #E8E0ED;
        }

        .student-table th {
            background: #321E48;
            color: #FFFFFF;
        }

        .student-table tbody tr:hover {
            background: #F5F0F9;
        }

        .table-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        .empty-message {
            text-align: center;
            padding: 25px;
            color: #666666;
        }

        @media (max-width: 600px) {
            .management-card {
                padding: 14px;
            }

            .management-header {
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

<div class="management-container">

    <div class="management-header">
        <div>
            <h1>Student Management</h1>
            <p>View, search, filter and manage student records.</p>
        </div>

        <div>
            <a href="Admin.php" class="management-btn secondary-btn">
                Back to Admin Dashboard
            </a>

            <a href="student_edit.php" class="management-btn">
                + Add Student
            </a>
        </div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="message-box <?= $messageType === 'error'
            ? 'message-error'
            : 'message-success' ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <section class="management-card">

        <h2>Search and Filter Students</h2>

        <form method="GET" action="students.php" class="filter-form">

            <div class="filter-field">
                <label for="search">Search</label>
                <input
                    type="search"
                    id="search"
                    name="search"
                    placeholder="Name, enrollment or email"
                    value="<?= e($search) ?>">
            </div>

            <div class="filter-field">
                <label for="department">Department</label>
                <select id="department" name="department">
                    <option value="">All Departments</option>

                    <?php foreach (
                        ['CE', 'IT', 'CSE', 'ME', 'EC', 'EE', 'CIVIL']
                        as $dept
                    ): ?>
                        <option
                            value="<?= e($dept) ?>"
                            <?= $department === $dept ? 'selected' : '' ?>>
                            <?= e($dept) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-field">
                <label for="semester">Semester</label>
                <select id="semester" name="semester">
                    <option value="">All Semesters</option>

                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <option
                            value="<?= $i ?>"
                            <?= $semester === (string) $i
                                ? 'selected'
                                : '' ?>>
                            Semester <?= $i ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <button type="submit" class="management-btn">
                Apply Filters
            </button>

            <a href="students.php"
               class="management-btn secondary-btn">
                Reset
            </a>

        </form>
    </section>

    <section class="management-card">

        <h2>Student Records (<?= count($students) ?>)</h2>

        <table class="student-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Enrollment</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Semester</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php if (count($students) > 0): ?>

                    <?php foreach ($students as $student): ?>

                        <tr>
                            <td><?= (int) $student['id'] ?></td>

                            <td><?= e($student['name']) ?></td>

                            <td><?= e($student['enrollment']) ?></td>

                            <td><?= e($student['email']) ?></td>

                            <td><?= e($student['department'] ?: 'Not set') ?></td>

                            <td><?= e($student['semester'] ?: 'Not set') ?></td>

                            <td>
                                <div class="table-actions">

                                    <a
                                        class="management-btn"
                                        href="student_edit.php?id=<?= (int) $student['id'] ?>">
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="backend/student_delete.php"
                                        onsubmit="return confirm('Are you sure you want to delete this student?');">

                                        <input
                                            type="hidden"
                                            name="student_id"
                                            value="<?= (int) $student['id'] ?>">

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e($_SESSION['csrf_token'] ?? '') ?>">

                                        <button
                                            type="submit"
                                            class="management-btn danger-btn">
                                            Delete
                                        </button>

                                    </form>

                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7" class="empty-message">
                            No student records found.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>
        </table>

    </section>

</div>

</body>
</html>
