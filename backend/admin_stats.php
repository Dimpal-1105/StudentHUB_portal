<?php
session_start();

header("Content-Type: application/json; charset=UTF-8");

if (
    !isset($_SESSION["student_id"]) ||
    !isset($_SESSION["student_role"]) ||
    $_SESSION["student_role"] !== "admin"
) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Access denied. Admin privileges required."
    ]);
    exit;
}

require_once __DIR__ . "/db.php";

try {
    // Count students
    $studentResult = $conn->query(
        "SELECT COUNT(*) AS total FROM students WHERE role = 'student'"
    );
    $totalStudents = (int) $studentResult->fetch_assoc()["total"];

    // Count events
    $eventResult = $conn->query(
        "SELECT COUNT(*) AS total FROM events"
    );
    $totalEvents = (int) $eventResult->fetch_assoc()["total"];

    // Count registrations
    $registrationResult = $conn->query(
        "SELECT COUNT(*) AS total FROM registrations"
    );
    $totalRegistrations = (int) $registrationResult->fetch_assoc()["total"];

    // Latest registrations
    $latestResult = $conn->query(
        "SELECT
            r.registered_at,
            s.name AS student_name,
            s.email AS student_email,
            e.title AS event_title
         FROM registrations r
         INNER JOIN students s ON r.student_id = s.id
         INNER JOIN events e ON r.event_id = e.id
         ORDER BY r.registered_at DESC
         LIMIT 5"
    );

    $latestRegistrations = [];

    while ($row = $latestResult->fetch_assoc()) {
        $latestRegistrations[] = $row;
    }

    echo json_encode([
        "success" => true,
        "statistics" => [
            "total_students" => $totalStudents,
            "total_events" => $totalEvents,
            "total_registrations" => $totalRegistrations
        ],
        "latest_registrations" => $latestRegistrations
    ]);

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to load dashboard statistics."
    ]);
}
?>
