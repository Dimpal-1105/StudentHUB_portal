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
    header('Location: ../manage_events.php');
    exit;
}

require_once __DIR__ . '/db.php';

function redirectWithMessage($message, $type = 'error') {
    $_SESSION['event_message'] = $message;
    $_SESSION['event_message_type'] = $type;
    header('Location: ../manage_events.php');
    exit;
}

/* Check CSRF token */
$sessionToken = $_SESSION['csrf_token'] ?? '';
$submittedToken = $_POST['csrf_token'] ?? '';

if (
    $sessionToken === '' ||
    $submittedToken === '' ||
    !hash_equals($sessionToken, $submittedToken)
) {
    http_response_code(403);
    exit('Invalid security token. Return to the event page and try again.');
}

/* Read and validate form fields */
$eventId = 0;

if (isset($_POST['event_id']) && $_POST['event_id'] !== '') {
    $eventId = filter_var($_POST['event_id'], FILTER_VALIDATE_INT);

    if (!$eventId || $eventId < 1) {
        redirectWithMessage('Invalid event selected.');
    }
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category = trim($_POST['category'] ?? '');
$eventDate = trim($_POST['event_date'] ?? '');
$location = trim($_POST['location'] ?? '');

$allowedCategories = [
    'Technical', 'Sports', 'Cultural', 'Academic', 'Other'
];

if (
    $title === '' ||
    $description === '' ||
    $eventDate === '' ||
    $location === '' ||
    !in_array($category, $allowedCategories, true)
) {
    redirectWithMessage('Please complete all required fields.');
}

if (
    mb_strlen($title) > 150 ||
    mb_strlen($description) > 5000 ||
    mb_strlen($location) > 200
) {
    redirectWithMessage('One or more fields exceed the allowed length.');
}

/* Convert datetime-local value to MySQL DATETIME */
$dateObject = DateTime::createFromFormat('!Y-m-d\TH:i', $eventDate);
$dateErrors = DateTime::getLastErrors();

if (
    !$dateObject ||
    ($dateErrors !== false &&
        ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) ||
    $dateObject->format('Y-m-d\TH:i') !== $eventDate
) {
    redirectWithMessage('Please enter a valid event date and time.');
}

$mysqlDate = $dateObject->format('Y-m-d H:i:s');

/* Load the current poster when editing */
$oldPoster = '';

if ($eventId > 0) {
    $stmt = $conn->prepare('SELECT poster FROM events WHERE id = ?');
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingEvent = $result->fetch_assoc();
    $stmt->close();

    if (!$existingEvent) {
        redirectWithMessage('Event not found.');
    }

    $oldPoster = $existingEvent['poster'] ?? '';
}

$posterPath = $oldPoster;
$newPosterAbsolutePath = '';
$uploadDirectory = dirname(__DIR__) . '/uploads/event_posters/';

/* Process an optional poster upload */
if (
    isset($_FILES['poster']) &&
    $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE
) {
    $file = $_FILES['poster'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        redirectWithMessage('Poster upload failed. Please try again.');
    }

    if ($file['size'] <= 0 || $file['size'] > 2 * 1024 * 1024) {
        redirectWithMessage('The poster must be smaller than 2 MB.');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        redirectWithMessage('Invalid uploaded file.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowedMimeTypes[$mimeType])) {
        redirectWithMessage('Only JPG, PNG, and WEBP posters are allowed.');
    }

    if (@getimagesize($file['tmp_name']) === false) {
        redirectWithMessage('The uploaded file is not a valid image.');
    }

    if (!is_dir($uploadDirectory)) {
        if (!mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
            redirectWithMessage('Could not create the poster upload folder.');
        }
    }

    if (!is_writable($uploadDirectory)) {
        redirectWithMessage('The poster upload folder is not writable.');
    }

    $newFileName = bin2hex(random_bytes(16)) . '.' . $allowedMimeTypes[$mimeType];
    $newPosterAbsolutePath = $uploadDirectory . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $newPosterAbsolutePath)) {
        redirectWithMessage('Could not save the poster. Please try again.');
    }

    $posterPath = 'uploads/event_posters/' . $newFileName;
}

/* Save the event */
try {
    if ($eventId > 0) {
        $stmt = $conn->prepare(
            'UPDATE events
             SET title = ?, description = ?, category = ?,
                 event_date = ?, location = ?, poster = ?
             WHERE id = ?'
        );

        $stmt->bind_param(
            'ssssssi',
            $title,
            $description,
            $category,
            $mysqlDate,
            $location,
            $posterPath,
            $eventId
        );

        $stmt->execute();
        $stmt->close();

        $successMessage = 'Event updated successfully.';
    } else {
        $stmt = $conn->prepare(
            'INSERT INTO events
             (title, description, category, event_date, location, poster)
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->bind_param(
            'ssssss',
            $title,
            $description,
            $category,
            $mysqlDate,
            $location,
            $posterPath
        );

        $stmt->execute();
        $stmt->close();

        $successMessage = 'Event added successfully.';
    }

    /* Remove the replaced poster only after saving succeeds */
    if (
        $newPosterAbsolutePath !== '' &&
        $oldPoster !== '' &&
        str_starts_with($oldPoster, 'uploads/event_posters/') &&
        basename($oldPoster) === substr($oldPoster, strlen('uploads/event_posters/'))
    ) {
        $oldPosterAbsolutePath = dirname(__DIR__) . '/' . $oldPoster;

        if (is_file($oldPosterAbsolutePath)) {
            @unlink($oldPosterAbsolutePath);
        }
    }

    redirectWithMessage($successMessage, 'success');

} catch (Throwable $e) {
    error_log($e->getMessage());

    if ($newPosterAbsolutePath !== '' && is_file($newPosterAbsolutePath)) {
        @unlink($newPosterAbsolutePath);
    }

    redirectWithMessage('Could not save the event. Please check the details and try again.');
}
?>