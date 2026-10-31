<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: Login.html');
    exit;
}

if (($_SESSION['student_role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied. Administrators only.');
}

require_once __DIR__ . '/backend/db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$event = [
    'id' => '',
    'title' => '',
    'description' => '',
    'category' => 'Technical',
    'event_date' => '',
    'location' => '',
    'poster' => ''
];

$isEdit = false;
$error = '';

if (isset($_GET['id'])) {
    $eventId = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if (!$eventId || $eventId < 1) {
        exit('Invalid event ID.');
    }

    $stmt = $conn->prepare(
        'SELECT id, title, description, category, event_date, location, poster
         FROM events WHERE id = ?'
    );
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingEvent = $result->fetch_assoc();
    $stmt->close();

    if (!$existingEvent) {
        exit('Event not found.');
    }

    $event = $existingEvent;
    $isEdit = true;
}

$eventDateValue = '';
if (!empty($event['event_date'])) {
    $eventDateValue = date(
        'Y-m-d\TH:i',
        strtotime($event['event_date'])
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit Event' : 'Add Event' ?> | StudentHUB</title>
    <link rel="stylesheet" href="Style.css">

    <style>
        .event-form-container {
            max-width: 760px;
            margin: 30px auto;
            padding: 28px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(50, 30, 72, 0.12);
        }

        .event-form-container h1 {
            color: #321E48;
            margin-top: 0;
        }

        .event-form {
            display: grid;
            gap: 16px;
        }

        .event-form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #321E48;
        }

        .event-form input,
        .event-form textarea,
        .event-form select {
            width: 100%;
            padding: 11px;
            border: 1px solid #cfc4dc;
            border-radius: 6px;
            box-sizing: border-box;
            font: inherit;
        }

        .event-form textarea {
            min-height: 120px;
            resize: vertical;
        }

        .event-form input:focus,
        .event-form textarea:focus,
        .event-form select:focus {
            outline: 2px solid #5B3B7A;
            outline-offset: 1px;
        }

        .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 8px;
        }

        .form-button {
            display: inline-block;
            padding: 11px 18px;
            border: 0;
            border-radius: 6px;
            background: #321E48;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font: inherit;
        }

        .form-button:hover {
            background: #5B3B7A;
        }

        .poster-preview {
            max-width: 180px;
            max-height: 140px;
            display: block;
            margin-top: 10px;
            object-fit: contain;
        }

        .form-note {
            color: #555;
            font-size: 0.9rem;
        }

        @media (max-width: 600px) {
            .event-form-container {
                margin: 16px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="event-form-container">
    <h1><?= $isEdit ? 'Edit Event' : 'Add New Event' ?></h1>
    <p>Enter the event information below.</p>

    <form
        class="event-form"
        action="backend/event_save.php"
        method="POST"
        enctype="multipart/form-data"
    >
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e($_SESSION['csrf_token']) ?>"
        >

        <input
            type="hidden"
            name="event_id"
            value="<?= e($event['id']) ?>"
        >

        <div>
            <label for="title">Event Title *</label>
            <input
                type="text"
                id="title"
                name="title"
                maxlength="150"
                required
                value="<?= e($event['title']) ?>"
            >
        </div>

        <div>
            <label for="category">Category *</label>
            <select id="category" name="category" required>
                <?php
                $categories = ['Technical', 'Sports', 'Cultural', 'Academic', 'Other'];
                foreach ($categories as $category):
                ?>
                    <option
                        value="<?= e($category) ?>"
                        <?= $event['category'] === $category ? 'selected' : '' ?>
                    >
                        <?= e($category) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="event_date">Date and Time *</label>
            <input
                type="datetime-local"
                id="event_date"
                name="event_date"
                required
                value="<?= e($eventDateValue) ?>"
            >
        </div>

        <div>
            <label for="location">Location *</label>
            <input
                type="text"
                id="location"
                name="location"
                maxlength="200"
                required
                value="<?= e($event['location']) ?>"
            >
        </div>

        <div>
            <label for="description">Description *</label>
            <textarea
                id="description"
                name="description"
                maxlength="5000"
                required
            ><?= e($event['description']) ?></textarea>
        </div>

        <div>
            <label for="poster">Event Poster</label>
            <input
                type="file"
                id="poster"
                name="poster"
                accept=".jpg,.jpeg,.png,.webp"
            >
            <p class="form-note">
                Accepted formats: JPG, JPEG, PNG, WEBP. Maximum size: 2 MB.
                <?= $isEdit ? 'Leave empty to keep the existing poster.' : '' ?>
            </p>

            <?php if ($isEdit && !empty($event['poster'])): ?>
                <p>Current poster:</p>
                <img
                    class="poster-preview"
                    src="<?= e($event['poster']) ?>"
                    alt="Current event poster"
                >
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="form-button">
                <?= $isEdit ? 'Update Event' : 'Save Event' ?>
            </button>

            <a href="manage_events.php" class="form-button">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>