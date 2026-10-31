<?php
session_start();

require_once __DIR__ . "/db.php";

// Accept POST requests only.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Register.html');
    exit;
}

// Escape output before displaying it in HTML.
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Collect and normalize form inputs.
$name       = trim($_POST['name'] ?? '');
$enrollment = strtoupper(trim($_POST['enrollment'] ?? ''));
$email      = strtolower(trim($_POST['email'] ?? ''));
$phone      = trim($_POST['phone'] ?? '');
$gender     = trim($_POST['gender'] ?? '');
$dob        = trim($_POST['dob'] ?? '');
$department = trim($_POST['department'] ?? '');
$semester   = trim($_POST['semester'] ?? '');
$address    = trim($_POST['address'] ?? '');
$password   = $_POST['password'] ?? '';
$confirm    = $_POST['confirmPassword'] ?? '';

$errors = [];

// 1. Validate name.
if ($name === '' || mb_strlen($name) > 100) {
    $errors[] = 'Enter a valid full name (maximum 100 characters).';
}

// 2. Validate enrollment number.
if (!preg_match('/^[A-Za-z0-9]{7,10}$/', $enrollment)) {
    $errors[] = 'Enrollment number must contain 7–10 letters or digits.';
}

// 3. Validate email.
if (!filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 150) {
    $errors[] = 'Enter a valid email address.';
}

// 4. Validate phone.
if (!preg_match('/^[0-9]{10}$/', $phone)) {
    $errors[] = 'Phone number must contain exactly 10 digits.';
}

// 5. Validate gender.
$allowedGenders = ['Male', 'Female', 'Other'];

if (!in_array($gender, $allowedGenders, true)) {
    $errors[] = 'Please select a valid gender.';
}

// 6. Validate date of birth.
$date = DateTime::createFromFormat('!Y-m-d', $dob);

if (
    !$date ||
    $date->format('Y-m-d') !== $dob ||
    $date > new DateTime('today')
) {
    $errors[] = 'Enter a valid date of birth that is not in the future.';
}

// 7. Validate department.
$allowedDepartments = ['CE', 'CSE', 'IT', 'ME', 'EC', 'CL'];

if (!in_array($department, $allowedDepartments, true)) {
    $errors[] = 'Please select a valid department.';
}

// 8. Validate semester.
$allowedSemesters = ['1', '2', '3', '4', '5', '6', '7', '8'];

if (!in_array($semester, $allowedSemesters, true)) {
    $errors[] = 'Please select a valid semester.';
}

// 9. Validate address.
if ($address === '' || mb_strlen($address) > 1000) {
    $errors[] = 'Address is required and must not exceed 1000 characters.';
}

// 10. Validate password.
if (strlen($password) < 8 || strlen($password) > 4096) {
    $errors[] = 'Password must contain at least 8 characters.';
}

if ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
}

$success = false;

if (empty($errors)) {
    try {
        // The existing form has no username field.
        // Use enrollment number as the unique username.
        $username = $enrollment;

        // Check whether email, enrollment, or username already exists.
        $check = $conn->prepare(
            "SELECT id FROM students
             WHERE email = ? OR enrollment = ? OR username = ?
             LIMIT 1"
        );

        $check->bind_param(
            "sss",
            $email,
            $enrollment,
            $username
        );

        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errors[] =
                'This email address, enrollment number, or username is already registered.';
        }

        $check->close();

        if (empty($errors)) {
            // Never store a plain-text password.
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            if ($hashedPassword === false) {
                throw new RuntimeException('Password hashing failed.');
            }

            // Public registration always creates a student account.
            $role = 'student';
            $semesterNumber = (int)$semester;

            // Insert using a prepared statement.
            $stmt = $conn->prepare(
                "INSERT INTO students
                (
                    name, username, email, password, enrollment,
                    phone, role, gender, dob, department,
                    semester, address
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssssssssssis",
                $name,
                $username,
                $email,
                $hashedPassword,
                $enrollment,
                $phone,
                $role,
                $gender,
                $dob,
                $department,
                $semesterNumber,
                $address
            );

            $stmt->execute();
            $stmt->close();

            $success = true;
        }

    } catch (mysqli_sql_exception $exception) {
        // Log technical details privately.
        error_log($exception->getMessage());

        if ($exception->getCode() === 1062) {
            $errors[] =
                'This email address or enrollment number is already registered.';
        } else {
            $errors[] =
                'Unable to complete registration right now. Please try again.';
        }

    } catch (Throwable $exception) {
        error_log($exception->getMessage());

        $errors[] =
            'An unexpected error occurred. Please try again.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHUB | Registration Result</title>

    <link rel="stylesheet" href="../Style.css">

    <style>
        .result-container {
            max-width: 650px;
            margin: 50px auto;
            padding: 25px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .result-container h2 {
            color: #321E48;
        }

        .error-list {
            color: #b00020;
            line-height: 1.8;
        }

        .success-message {
            color: #18733b;
            line-height: 1.8;
        }

        .result-container a {
            display: inline-block;
            margin: 10px 10px 0 0;
            color: #321E48;
        }
    </style>
</head>

<body>

    <main class="result-container">

        <?php if ($success): ?>

            <h2>Registration Successful!</h2>

            <p class="success-message">
                Welcome to StudentHUB, <?= e($name) ?>!
                Your details have been saved successfully.
            </p>

            <p>
                <strong>Enrollment Number:</strong>
                <?= e($enrollment) ?>
            </p>

            <a href="../Login.html">Proceed to Login</a>

        <?php else: ?>

            <h2>Registration Failed</h2>

            <p>Please correct the following problems:</p>

            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>

            <a href="../Register.html">Return to Registration</a>

        <?php endif; ?>

        <br>

        <a href="../home.html">Back to Home</a>

    </main>

</body>
</html>