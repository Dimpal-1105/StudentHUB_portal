<?php

session_start();

require_once __DIR__ . "/db.php";

// Accept POST requests only.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Login.html');
    exit;
}

// Escape output before displaying it in HTML.
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];

if ($username === '' || strlen($username) > 150) {
    $errors[] = "Enter your enrollment number or email address.";
}

if ($password === '' || strlen($password) > 4096) {
    $errors[] = "Enter a valid password.";
}

$student = null;

if (empty($errors)) {
    try {
        // Find the account using its enrollment number or email.
        $stmt = $conn->prepare(
            "SELECT id, name, username, email, password, role
             FROM students
             WHERE username = ? OR email = ?
             LIMIT 1"
        );

        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $student = $result->fetch_assoc();

        $stmt->close();

        // Verify the submitted password against the stored hash.
        if (
            !$student ||
            !password_verify($password, $student['password'])
        ) {
            $errors[] = "Invalid enrollment number/email or password.";
            $student = null;
        }

    } catch (mysqli_sql_exception $exception) {
        error_log($exception->getMessage());

        $errors[] = "Login is temporarily unavailable. Please try again.";
    }
}

if ($student !== null && empty($errors)) {

    // Prevent session fixation.
    session_regenerate_id(true);

    // Store only the information needed for the session.
    $_SESSION['student_id'] = (int)$student['id'];
    $_SESSION['student_name'] = $student['name'];
    $_SESSION['student_email'] = $student['email'];
    $_SESSION['student_role'] = $student['role'];
    $_SESSION['last_activity'] = time();

    // Redirect according to the account's database role.
    if ($student['role'] === 'admin') {
        header('Location: ../Admin.php');
    } else {
        header('Location: ../Dashboard.php');
    }

    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHUB | Login Result</title>
    <link rel="stylesheet" href="../Style.css">

    <style>
        .login-result {
            max-width: 600px;
            margin: 50px auto;
            padding: 25px;
            background: #fcf4e2;
            border: 2px solid #5B3B7A;
            border-radius: 10px;
            text-align: center;
        }

        .login-result h2 {
            color: #321E48;
        }

        .error-message {
            color: #b00020;
            line-height: 1.8;
        }

        .login-result a {
            display: inline-block;
            margin: 10px;
            color: #321E48;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <main class="login-result">

        <h2>Login Failed</h2>

        <ul class="error-message">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>

        <a href="../Login.html">Try Again</a>
        <a href="../home.html">Home</a>

    </main>

</body>
</html>