<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

try {
    $sql = "SELECT
                id,
                title,
                category,
                DATE_FORMAT(event_date, '%Y-%m-%d') AS date,
                TIME_FORMAT(event_date, '%h:%i %p') AS time,
                location,
                description
            FROM events
            ORDER BY event_date ASC";

    $result = $conn->query($sql);

    $events = [];

    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    echo json_encode(
        $events,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

} catch (Throwable $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'error' => 'Unable to load events.'
    ]);
}
?>