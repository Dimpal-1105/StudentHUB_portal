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

$eventId = filter_var($_POST['event_id'] ?? null, FILTER_VALIDATE_INT);

if (!$eventId || $eventId < 1) {
    $_SESSION['event_message'] = 'Invalid event selected.';
    $_SESSION['event_message_type'] = 'error';
    header('Location: ../manage_events.php');
    exit;
}

try {
    $stmt = $conn->prepare('SELECT poster FROM events WHERE id = ?');
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    $stmt->close();

    if (!$event) {
        $_SESSION['event_message'] = 'Event not found.';
        $_SESSION['event_message_type'] = 'error';
    } else {
        $stmt = $conn->prepare('DELETE FROM events WHERE id = ?');
        $stmt->bind_param('i', $eventId);
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();

        if ($deleted > 0) {
            $poster = $event['poster'] ?? '';

            if (
                $poster !== '' &&
                str_starts_with($poster, 'uploads/event_posters/') &&
                basename($poster) === substr($poster, strlen('uploads/event_posters/'))
            ) {
                $posterAbsolutePath = dirname(__DIR__) . '/' . $poster;

                if (is_file($posterAbsolutePath)) {
                    @unlink($posterAbsolutePath);
                }
            }

            $_SESSION['event_message'] = 'Event deleted successfully.';
            $_SESSION['event_message_type'] = 'success';
        } else {
            $_SESSION['event_message'] = 'The event could not be deleted.';
            $_SESSION['event_message_type'] = 'error';
        }
    }
} catch (Throwable $e) {
    error_log($e->getMessage());
    $_SESSION['event_message'] = 'Could not delete the event. Please try again.';
    $_SESSION['event_message_type'] = 'error';
}

header('Location: ../manage_events.php');
exit;
?>