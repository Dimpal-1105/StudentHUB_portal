<?php
session_start();

// Accept POST requests only.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../contact.html');
    exit;
}

// Collect form data.
$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];

// Validate inputs.
if ($name === '' || strlen($name) > 100) {
    $errors[] = 'Please enter a valid name (maximum 100 characters).';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if ($subject === '' || strlen($subject) > 200) {
    $errors[] = 'Please enter a subject (maximum 200 characters).';
}

if ($message === '' || strlen($message) > 10000) {
    $errors[] = 'Please enter a message (maximum 10,000 characters).';
}

// JSON storage location.
$dataFile = dirname(__DIR__) . '/data/contacts.json';

if (empty($errors)) {

    if (!is_file($dataFile) || !is_writable($dataFile)) {
        $errors[] = 'Message storage is unavailable. Check the data folder and file permissions.';
    } else {

        $handle = fopen($dataFile, 'c+');

        if ($handle === false || !flock($handle, LOCK_EX)) {

            if (is_resource($handle)) {
                fclose($handle);
            }

            $errors[] = 'Unable to save your message. Please try again.';

        } else {

            rewind($handle);
            $contents = stream_get_contents($handle);

            $contacts = json_decode($contents ?: '[]', true);

            if (!is_array($contacts) ||
                json_last_error() !== JSON_ERROR_NONE) {

                $errors[] = 'The contacts JSON file contains invalid data.';

            } else {

                $newMessage = [
                    'id' => bin2hex(random_bytes(8)),
                    'name' => $name,
                    'email' => $email,
                    'subject' => $subject,
                    'message' => $message,
                    'created_at' => date('c')
                ];

                $contacts[] = $newMessage;

                $json = json_encode(
                    $contacts,
                    JSON_PRETTY_PRINT |
                    JSON_UNESCAPED_UNICODE |
                    JSON_INVALID_UTF8_SUBSTITUTE
                );

                if (
                    $json === false ||
                    !ftruncate($handle, 0) ||
                    !rewind($handle) ||
                    fwrite($handle, $json) !== strlen($json) ||
                    !fflush($handle)
                ) {
                    $errors[] = 'Your message could not be saved. Please try again.';
                }
            }

            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}

$success = empty($errors);

// Safely display user-provided text.
function e($value) {
    return htmlspecialchars(
        (string)$value,
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

    <title>StudentHUB | Contact Result</title>

    <link rel="stylesheet" href="../Style.css">

    <style>
        .result-container {
            max-width: 650px;
            margin: 50px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .result-container h2 {
            color: #321E48;
        }

        .success-message {
            color: #18733b;
            line-height: 1.7;
        }

        .error-list {
            color: #b00020;
            line-height: 1.8;
        }

        .result-container a {
            display: inline-block;
            margin: 12px 12px 0 0;
            padding: 10px 16px;
            background: #5B3B7A;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .result-container a:hover {
            background: #321E48;
        }
    </style>
</head>

<body>

    <main class="result-container">

        <?php if ($success): ?>

            <h2>Message Submitted Successfully!</h2>

            <p class="success-message">
                Thank you, <?= e($name) ?>!
                Your message has been saved successfully.
            </p>

            <p>
                Our support team can review your message
                from the stored contact records.
            </p>

        <?php else: ?>

            <h2>Unable to Submit Message</h2>

            <p>Please correct the following problems:</p>

            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>

        <?php endif; ?>

        <a href="../contact.html">Back to Contact</a>
        <a href="../home.html">Home</a>

    </main>

</body>
</html>