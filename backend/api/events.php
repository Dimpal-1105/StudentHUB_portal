<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

/* Authentication */
if (!isset($_SESSION['student_id'])) {
    respond(401, ['success' => false, 'message' => 'Please log in first.']);
}

if (($_SESSION['student_role'] ?? '') !== 'admin') {
    respond(403, ['success' => false, 'message' => 'Administrators only.']);
}

/* Initialize CSRF token */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$method = $_SERVER['REQUEST_METHOD'];

/* Connect to the database */
require_once __DIR__ . '/../db.php';

/* Read JSON input */
$input = [];

if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    if (!is_array($input)) {
        respond(400, [
            'success' => false,
            'message' => 'Invalid JSON request.'
        ]);
    }

    /* Verify CSRF token for changes */
    $submittedToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (
        $sessionToken === '' ||
        $submittedToken === '' ||
        !hash_equals($sessionToken, $submittedToken)
    ) {
        respond(403, [
            'success' => false,
            'message' => 'Invalid security token. Refresh the page and try again.'
        ]);
    }
}

/* Validate event details */
function validateEvent($input) {
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $category = trim($input['category'] ?? '');
    $eventDate = trim($input['event_date'] ?? '');
    $location = trim($input['location'] ?? '');

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
        respond(422, [
            'success' => false,
            'message' => 'Please provide all required event details and a valid category.'
        ]);
    }

    if (
        mb_strlen($title) > 150 ||
        mb_strlen($description) > 5000 ||
        mb_strlen($location) > 200
    ) {
        respond(422, [
            'success' => false,
            'message' => 'One or more event fields exceed the allowed length.'
        ]);
    }

    /* Accept a datetime-local value or a MySQL DATETIME value */
    $dateObject = DateTime::createFromFormat('!Y-m-d\TH:i', $eventDate);

    if (!$dateObject) {
        $dateObject = DateTime::createFromFormat('!Y-m-d H:i:s', $eventDate);
    }

    $dateErrors = DateTime::getLastErrors();

    if (
        !$dateObject ||
        ($dateErrors !== false &&
            ($dateErrors['warning_count'] > 0 ||
             $dateErrors['error_count'] > 0))
    ) {
        respond(422, [
            'success' => false,
            'message' => 'Enter a valid event date and time.'
        ]);
    }

    return [
        $title,
        $description,
        $category,
        $dateObject->format('Y-m-d H:i:s'),
        $location
    ];
}

/* Handle requests */
try {
    switch ($method) {

        /* GET: List all events */
        case 'GET':
            $result = $conn->query(
                'SELECT id, title, description, category,
                        event_date, location, poster, created_at
                 FROM events
                 ORDER BY event_date DESC'
            );

            $events = [];

            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }

            respond(200, [
                'success' => true,
                'csrf_token' => $_SESSION['csrf_token'],
                'count' => count($events),
                'events' => $events
            ]);
            break;

        /* POST: Add an event */
        case 'POST':
            $details = validateEvent($input);

            $poster = null;

            $stmt = $conn->prepare(
                'INSERT INTO events
                 (title, description, category, event_date, location, poster)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );

            $stmt->bind_param(
                'ssssss',
                $details[0],
                $details[1],
                $details[2],
                $details[3],
                $details[4],
                $poster
            );

            $stmt->execute();
            $newId = $conn->insert_id;
            $stmt->close();

            respond(201, [
                'success' => true,
                'message' => 'Event added successfully.',
                'id' => $newId
            ]);
            break;

        /* PUT: Update an event */
        case 'PUT':
            $eventId = filter_var(
                $input['id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$eventId || $eventId < 1) {
                respond(422, [
                    'success' => false,
                    'message' => 'A valid event ID is required.'
                ]);
            }

            $details = validateEvent($input);

            $stmt = $conn->prepare(
                'UPDATE events
                 SET title = ?, description = ?, category = ?,
                     event_date = ?, location = ?
                 WHERE id = ?'
            );

            $stmt->bind_param(
                'sssssi',
                $details[0],
                $details[1],
                $details[2],
                $details[3],
                $details[4],
                $eventId
            );

            $stmt->execute();
            $updated = $stmt->affected_rows;
            $stmt->close();

            /* Distinguish a missing event from an unchanged event */
            if ($updated === 0) {
                $check = $conn->prepare(
                    'SELECT id FROM events WHERE id = ?'
                );
                $check->bind_param('i', $eventId);
                $check->execute();
                $exists = $check->get_result()->num_rows > 0;
                $check->close();

                if (!$exists) {
                    respond(404, [
                        'success' => false,
                        'message' => 'Event not found.'
                    ]);
                }
            }

            respond(200, [
                'success' => true,
                'message' => 'Event updated successfully.'
            ]);
            break;

        /* DELETE: Delete an event */
        case 'DELETE':
            $eventId = filter_var(
                $input['id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$eventId || $eventId < 1) {
                respond(422, [
                    'success' => false,
                    'message' => 'A valid event ID is required.'
                ]);
            }

            $stmt = $conn->prepare(
                'SELECT poster FROM events WHERE id = ?'
            );
            $stmt->bind_param('i', $eventId);
            $stmt->execute();
            $event = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$event) {
                respond(404, [
                    'success' => false,
                    'message' => 'Event not found.'
                ]);
            }

            $stmt = $conn->prepare('DELETE FROM events WHERE id = ?');
            $stmt->bind_param('i', $eventId);
            $stmt->execute();
            $deleted = $stmt->affected_rows;
            $stmt->close();

            if ($deleted > 0) {
                /* Remove only posters stored in our upload folder */
                $poster = $event['poster'] ?? '';

                if (
                    $poster !== '' &&
                    str_starts_with($poster, 'uploads/event_posters/') &&
                    basename($poster) === substr(
                        $poster,
                        strlen('uploads/event_posters/')
                    )
                ) {
                    $posterPath = dirname(__DIR__, 2) . '/' . $poster;

                    if (is_file($posterPath)) {
                        @unlink($posterPath);
                    }
                }

                respond(200, [
                    'success' => true,
                    'message' => 'Event deleted successfully.'
                ]);
            }

            respond(500, [
                'success' => false,
                'message' => 'The event could not be deleted.'
            ]);
            break;

        default:
            header('Allow: GET, POST, PUT, DELETE');

            respond(405, [
                'success' => false,
                'message' => 'Method not allowed.'
            ]);
    }

} catch (Throwable $e) {
    error_log($e->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'A server error occurred while processing the request.'
    ]);
}
?>