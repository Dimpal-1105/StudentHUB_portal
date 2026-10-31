<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: ../Login.html');
    exit;
}

if (($_SESSION['student_role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied. Administrators only.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../students.php');
    exit;
}

require_once __DIR__ . '/db.php';

// Validate CSRF token.
$sessionToken = $_SESSION['csrf_token'] ?? '';
$submittedToken = $_POST['csrf_token'] ?? '';

if (
    $sessionToken === '' ||
    $submittedToken === '' ||
    !hash_equals($sessionToken, $submittedToken)
) {
    http_response_code(403);
    exit('Invalid security token. Refresh the page and try again.');
}

// Validate student ID.
$studentId = filter_var(
    $_POST['student_id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$studentId || $studentId < 1) {
    $_SESSION['student_message'] = 'Invalid student selected.';
    $_SESSION['student_message_type'] = 'error';

    header('Location: ../students.php');
    exit;
}

try {
    // Delete only student accounts, never administrator accounts.
    $stmt = $conn->prepare(
        "DELETE FROM students WHERE id = ? AND role = 'student'"
    );

    $stmt->bind_param('i', $studentId);
    $stmt->execute();

    $deleted = $stmt->affected_rows;
    $stmt->close();

    if ($deleted > 0) {
        $_SESSION['student_message'] = 'Student deleted successfully.';
        $_SESSION['student_message_type'] = 'success';
    } else {
        $_SESSION['student_message'] = 'Student not found or already deleted.';
        $_SESSION['student_message_type'] = 'error';
    }

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());

    $_SESSION['student_message'] =
        'Unable to delete this student. Please try again.';

    $_SESSION['student_message_type'] = 'error';
}

header('Location: ../students.php');
exit;