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
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$message = $_SESSION['event_message'] ?? '';
$messageType = $_SESSION['event_message_type'] ?? 'success';

unset($_SESSION['event_message'], $_SESSION['event_message_type']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Event Management | StudentHub</title>

    <link rel="stylesheet" href="Style.css">

    <style>
        .event-management {
            width: 95%;
            max-width: 1400px;
            margin: 25px auto;
        }

        .event-management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .event-management h1 {
            color: #321E48;
        }

        .event-management-card {
            background: #FFFFFF;
            padding: 22px;
            border-radius: 12px;
            margin-bottom: 22px;
            box-shadow: 0 3px 12px rgba(50, 30, 72, 0.10);
            overflow-x: auto;
        }

        .event-management-btn {
            display: inline-block;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            background: #321E48;
            color: #FFFFFF;
            text-decoration: none;
            cursor: pointer;
        }

        .event-management-btn:hover {
            background: #5B3B7A;
        }

        .event-management-btn.secondary {
            background: #EDE6F3;
            color: #321E48;
        }

        .event-management-btn.danger {
            background: #A52A3A;
        }

        .event-management-table {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
        }

        .event-management-table th,
        .event-management-table td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #E8E0ED;
        }

        .event-management-table th {
            background: #321E48;
            color: #FFFFFF;
        }

        .event-management-table tr:hover {
            background: #F5F0F9;
        }

        .event-poster-thumbnail {
            width: 65px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }

        .event-management-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .event-management-message {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 18px;
        }

        .event-management-message.success {
            background: #E4F5E9;
            color: #216638;
        }

        .event-management-message.error {
            background: #FBE8E8;
            color: #922B35;
        }
        /* Event Management Action Buttons */
.api-edit-button,
.api-delete-button {
    display: inline-block;
    padding: 8px 14px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    font-family: inherit;
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

/* Edit Button */
.api-edit-button {
    background-color: #5B3B7A;
    color: #FFFFFF;
}

.api-edit-button:hover {
    background-color: #321E48;
    transform: translateY(-1px);
}

/* Delete Button */
.api-delete-button {
    background-color: #A52A3A;
    color: #FFFFFF;
}

.api-delete-button:hover {
    background-color: #821E2C;
    transform: translateY(-1px);
}

/* Keep buttons aligned inside the table */
.event-management-table td:last-child {
    white-space: nowrap;
}

.api-edit-button:focus-visible,
.api-delete-button:focus-visible {
    outline: 2px solid #321E48;
    outline-offset: 3px;
}
    </style>
</head>

<body>

<div class="event-management">

    <div class="event-management-header">
        <header>
            <h1>Event Management</h1>
            <p>Add, update, view and manage campus events.</p>
        </header>

        <div>
            <a href="Admin.php"
               class="event-management-btn secondary">
                Admin Dashboard
            </a>

            <a href="event_edit.php"
               class="event-management-btn">
                + Add Event
            </a>
        </div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="event-management-message <?= $messageType === 'error' ? 'error' : 'success' ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>
<section class="event-management-card">

    <h2>All Events</h2>
    <p id="eventApiMessage"
       role="status"
       aria-live="polite"
       style="display:none;margin-top:15px;">
    </p>

    <div style="overflow-x:auto;margin-top:20px;">
        <table class="event-management-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Poster</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody id="eventsTableBody">
                <tr>
                    <td colspan="6">Loading events...</td>
                </tr>
            </tbody>
        </table>
    </div>

</section>

<div id="eventApiModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);
            z-index:1000;overflow-y:auto;padding:20px;">

    <div style="background:white;max-width:650px;margin:30px auto;padding:25px;
                border-radius:10px;">

        <h2 id="eventApiFormTitle">Add Event</h2>

        <form id="eventApiForm">
            <input type="hidden" id="apiEventId">

            <label for="apiTitle">Event Title *</label>
            <input id="apiTitle" type="text" maxlength="150" required
                   style="display:block;width:100%;padding:10px;margin:6px 0 15px;">

            <label for="apiCategory">Category *</label>
            <select id="apiCategory" required
                    style="display:block;width:100%;padding:10px;margin:6px 0 15px;">
                <option value="Technical">Technical</option>
                <option value="Sports">Sports</option>
                <option value="Cultural">Cultural</option>
                <option value="Academic">Academic</option>
                <option value="Other">Other</option>
            </select>

            <label for="apiEventDate">Date and Time *</label>
            <input id="apiEventDate" type="datetime-local" required
                   style="display:block;width:100%;padding:10px;margin:6px 0 15px;">

            <label for="apiLocation">Location *</label>
            <input id="apiLocation" type="text" maxlength="200" required
                   style="display:block;width:100%;padding:10px;margin:6px 0 15px;">

            <label for="apiDescription">Description *</label>
            <textarea id="apiDescription" maxlength="5000" required
                      style="display:block;width:100%;min-height:100px;padding:10px;
                             margin:6px 0 15px;"></textarea>

            <button type="submit" class="event-management-btn">
                Save Event
            </button>

            <button type="button"
                    class="event-management-btn secondary"
                    onclick="closeEventForm()">
                Cancel
            </button>
        </form>
    </div>
</div>

<script src="event_api.js" defer></script>
</div>

</body>
</html>
