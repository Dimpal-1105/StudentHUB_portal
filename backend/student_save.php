<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: ../Login.html');
    exit;
}

if (($_SESSION['student_role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../students.php');
    exit;
}

require_once __DIR__ . '/db.php';

function redirectWithMessage($message, $type = 'error') {
    $_SESSION['student_message'] = $message;
    $_SESSION['student_message_type'] = $type;

    header('Location: ../students.php');
    exit;
}

if (
    empty($_SESSION['csrf_token']) ||
    !isset($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit('Invalid security token. Refresh the page and try again.');
}

$studentId = filter_var(
    $_POST['student_id'] ?? '',
    FILTER_VALIDATE_INT
);

$isEditing = $studentId !== false
    && $studentId !== null
    && $studentId > 0;

$name = trim($_POST['name'] ?? '');
$enrollment = strtoupper(trim($_POST['enrollment'] ?? ''));
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$dob = trim($_POST['dob'] ?? '');
$department = trim($_POST['department'] ?? '');
$semesterInput = trim($_POST['semester'] ?? '');
$address = trim($_POST['address'] ?? '');
$password = $_POST['password'] ?? '';

if (
    $name === '' ||
    strlen($name) > 100 ||
    !preg_match("/^[\p{L}\p{M} .'-]+$/u", $name)
) {
    redirectWithMessage('Enter a valid student name.');
}

if (!preg_match('/^[0-9]{2}[A-Z]{2}[0-9]{3,6}$/', $enrollment)) {
    redirectWithMessage('Enter a valid enrollment number, such as 25CE063.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
    redirectWithMessage('Enter a valid email address.');
}

if (strlen($phone) > 15) {
    redirectWithMessage('Phone number must not exceed 15 characters.');
}

if ($phone !== '' && !preg_match('/^[0-9+() -]{7,15}$/', $phone)) {
    redirectWithMessage('Enter a valid phone number.');
}

if (!in_array($gender, ['', 'Male', 'Female', 'Other'], true)) {
    redirectWithMessage('Select a valid gender.');
}

if (!in_array($department, ['', 'CE', 'IT', 'CSE', 'ME', 'EC', 'EE', 'CIVIL'], true)) {
    redirectWithMessage('Select a valid department.');
}

$semester = null;

if ($semesterInput !== '') {
    $semester = filter_var($semesterInput, FILTER_VALIDATE_INT);

    if ($semester === false || $semester < 1 || $semester > 8) {
        redirectWithMessage('Select a valid semester.');
    }
}

if (strlen($address) > 1000) {
    redirectWithMessage('Address must not exceed 1000 characters.');
}

if ($dob !== '') {
    $date = DateTime::createFromFormat('!Y-m-d', $dob);

    if (!$date || $date->format('Y-m-d') !== $dob || $dob > date('Y-m-d')) {
        redirectWithMessage('Enter a valid date of birth.');
    }
}

if ($password !== '' && (strlen($password) < 8 || strlen($password) > 128)) {
    redirectWithMessage('Password must be between 8 and 128 characters.');
}

if (!$isEditing && $password === '') {
    redirectWithMessage('An initial password is required for a new student.');
}

// Check duplicate email or enrollment number.
$sql = "SELECT id FROM students WHERE (email = ? OR enrollment = ?)";
$params = [$email, $enrollment];
$types = 'ss';

if ($isEditing) {
    $sql .= " AND id <> ?";
    $params[] = $studentId;
    $types .= 'i';
}

$check = $conn->prepare($sql);
$check->bind_param($types, ...$params);
$check->execute();

$duplicate = $check->get_result()->num_rows > 0;
$check->close();

if ($duplicate) {
    redirectWithMessage('That email address or enrollment number is already in use.');
}

$genderValue = $gender !== '' ? $gender : null;
$dobValue = $dob !== '' ? $dob : null;
$departmentValue = $department !== '' ? $department : null;
$semesterValue = $semester;
$addressValue = $address !== '' ? $address : null;

try {
    if ($isEditing) {
        // Confirm the record exists and belongs to a student account.
        $checkStudent = $conn->prepare(
            "SELECT id FROM students WHERE id = ? AND role = 'student'"
        );
        $checkStudent->bind_param('i', $studentId);
        $checkStudent->execute();

        $exists = $checkStudent->get_result()->num_rows === 1;
        $checkStudent->close();

        if (!$exists) {
            redirectWithMessage('Student record not found.');
        }

        if ($password !== '') {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "UPDATE students
                 SET name = ?, username = ?, enrollment = ?, email = ?,
                     phone = ?, gender = ?, dob = ?, department = ?,
                     semester = ?, address = ?, password = ?
                 WHERE id = ? AND role = 'student'"
            );

            $stmt->bind_param(
                'ssssssssissi',
                $name,
                $enrollment,
                $enrollment,
                $email,
                $phone,
                $genderValue,
                $dobValue,
                $departmentValue,
                $semesterValue,
                $addressValue,
                $passwordHash,
                $studentId
            );
        } else {
            $stmt = $conn->prepare(
                "UPDATE students
                 SET name = ?, username = ?, enrollment = ?, email = ?,
                     phone = ?, gender = ?, dob = ?, department = ?,
                     semester = ?, address = ?
                 WHERE id = ? AND role = 'student'"
            );

            $stmt->bind_param(
                'ssssssssisi',
                $name,
                $enrollment,
                $enrollment,
                $email,
                $phone,
                $genderValue,
                $dobValue,
                $departmentValue,
                $semesterValue,
                $addressValue,
                $studentId
            );
        }

        $stmt->execute();
        $stmt->close();

        redirectWithMessage('Student details updated successfully.', 'success');

    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO students
                (name, username, email, password, enrollment,
                 phone, gender, dob, department, semester, address, role)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'student')"
        );

        $stmt->bind_param(
            'sssssssssis',
            $name,
            $enrollment,
            $email,
            $passwordHash,
            $enrollment,
            $phone,
            $genderValue,
            $dobValue,
            $departmentValue,
            $semesterValue,
            $addressValue
        );

        $stmt->execute();
        $stmt->close();

        redirectWithMessage('Student added successfully.', 'success');
    }

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());

    if ((int) $e->getCode() === 1062) {
        redirectWithMessage('That email address or enrollment number is already in use.');
    }

    redirectWithMessage('Unable to save the student. Please try again.');
}
