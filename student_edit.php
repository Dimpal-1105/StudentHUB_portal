<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: Login.html');
    exit;
}

if (($_SESSION['student_role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied. Only administrators can manage students.');
}

// Initialize CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/backend/db.php';

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$studentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$isEditing = $studentId !== null && $studentId !== false && $studentId > 0;

$student = [
    'name' => '',
    'enrollment' => '',
    'email' => '',
    'phone' => '',
    'gender' => '',
    'dob' => '',
    'department' => '',
    'semester' => '',
    'address' => ''
];

if ($isEditing) {
    $stmt = $conn->prepare(
        "SELECT name, enrollment, email, phone, gender, dob,
                department, semester, address
         FROM students
         WHERE id = ? AND role = 'student'"
    );
    $stmt->bind_param('i', $studentId);
    $stmt->execute();

    $existingStudent = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$existingStudent) {
        http_response_code(404);
        exit('Student record not found.');
    }

    $student = $existingStudent;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $isEditing ? 'Edit Student' : 'Add Student' ?> | StudentHub</title>

    <link rel="stylesheet" href="Style.css">

    <style>
        .student-form-container {
            width: 92%;
            max-width: 850px;
            margin: 30px auto;
        }

        .student-form-card {
            background: #FFFFFF;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(50, 30, 72, 0.10);
        }

        .student-form-card h1 {
            color: #321E48;
            margin-top: 0;
        }

        .student-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .student-form-field {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .student-form-field.full-width {
            grid-column: 1 / -1;
        }

        .student-form-field label {
            font-weight: 600;
            color: #321E48;
        }

        .student-form-field input,
        .student-form-field select,
        .student-form-field textarea {
            box-sizing: border-box;
            width: 100%;
            padding: 11px;
            border: 1px solid #D5C9DF;
            border-radius: 6px;
            font: inherit;
        }

        .student-form-field input:focus,
        .student-form-field select:focus,
        .student-form-field textarea:focus {
            outline: 2px solid #5B3B7A;
            outline-offset: 1px;
        }

        .student-form-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .student-form-btn {
            display: inline-block;
            padding: 11px 18px;
            border: none;
            border-radius: 6px;
            background: #321E48;
            color: #FFFFFF;
            text-decoration: none;
            cursor: pointer;
            font-size: 15px;
        }

        .student-form-btn:hover {
            background: #5B3B7A;
        }

        .student-form-btn.cancel {
            background: #EDE6F3;
            color: #321E48;
        }

        .form-note {
            color: #666666;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .student-form-grid {
                grid-template-columns: 1fr;
            }

            .student-form-card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<div class="student-form-container">

    <section class="student-form-card">

        <h1><?= $isEditing ? 'Edit Student' : 'Add New Student' ?></h1>

        <p>
            <?= $isEditing
                ? 'Update the student information below.'
                : 'Enter the student details to create a new account.' ?>
        </p>

        <form action="backend/student_save.php" method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_token']) ?>">

            <input
                type="hidden"
                name="student_id"
                value="<?= $isEditing ? (int)$studentId : '' ?>">

            <div class="student-form-grid">

                <div class="student-form-field">
                    <label for="name">Full Name *</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        maxlength="100"
                        required
                        value="<?= e($student['name']) ?>">
                </div>

                <div class="student-form-field">
                    <label for="enrollment">Enrollment Number *</label>
                    <input
                        type="text"
                        id="enrollment"
                        name="enrollment"
                        maxlength="30"
                        pattern="[0-9]{2}[A-Z]{2}[0-9]{3,6}"
                        title="Example: 25CE063"
                        required
                        value="<?= e($student['enrollment']) ?>">
                </div>

                <div class="student-form-field">
                    <label for="email">Email Address *</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        maxlength="150"
                        required
                        value="<?= e($student['email']) ?>">
                </div>

                <div class="student-form-field">
                    <label for="phone">Phone Number</label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        maxlength="15"
                        value="<?= e($student['phone']) ?>">
                </div>

                <div class="student-form-field">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="">Select</option>
                        <?php foreach (['Male', 'Female', 'Other'] as $option): ?>
                            <option
                                value="<?= e($option) ?>"
                                <?= $student['gender'] === $option ? 'selected' : '' ?>>
                                <?= e($option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="student-form-field">
                    <label for="dob">Date of Birth</label>
                    <input
                        type="date"
                        id="dob"
                        name="dob"
                        value="<?= e($student['dob']) ?>">
                </div>

                <div class="student-form-field">
                    <label for="department">Department</label>
                    <select id="department" name="department">
                        <option value="">Select Department</option>
                        <?php foreach (['CE', 'IT', 'CSE', 'ME', 'EC', 'EE', 'CIVIL'] as $option): ?>
                            <option
                                value="<?= e($option) ?>"
                                <?= $student['department'] === $option ? 'selected' : '' ?>>
                                <?= e($option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="student-form-field">
                    <label for="semester">Semester</label>
                    <select id="semester" name="semester">
                        <option value="">Select Semester</option>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option
                                value="<?= $i ?>"
                                <?= (string)$student['semester'] === (string)$i ? 'selected' : '' ?>>
                                Semester <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="student-form-field full-width">
                    <label for="address">Address</label>
                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        maxlength="1000"><?= e($student['address']) ?></textarea>
                </div>

                <div class="student-form-field full-width">
                    <label for="password">
                        <?= $isEditing ? 'New Password (optional)' : 'Initial Password *' ?>
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="8"
                        maxlength="128"
                        <?= $isEditing ? '' : 'required' ?>
                        autocomplete="new-password">

                    <span class="form-note">
                        <?= $isEditing
                            ? 'Leave blank to keep the current password.'
                            : 'Use at least 8 characters.' ?>
                    </span>
                </div>

            </div>

            <div class="student-form-actions">
                <button type="submit" class="student-form-btn">
                    <?= $isEditing ? 'Save Changes' : 'Add Student' ?>
                </button>

                <a href="students.php" class="student-form-btn cancel">
                    Cancel
                </a>
            </div>

        </form>

    </section>

</div>

</body>
</html>
