<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: Login.html');
    exit;
}

require_once __DIR__ . '/backend/db.php';

$stmt = $conn->prepare(
    "SELECT name, enrollment, email, phone, gender, dob,
            department, semester, address
     FROM students
     WHERE id = ?"
);

$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();

$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    http_response_code(404);
    exit('Student profile not found.');
}

function escapeProfile($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub | Profile</title>

   <link rel="stylesheet" href="Style.css">
</head>
<style>
    .profile-section {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 20px;
    margin-top: 25px;
    align-items: stretch;
}

.profile-card {
    background-color: #321E48;
    color: white;
    text-align: center;
    padding: 30px 20px;
    border-radius: 8px;
    grid-row: 1 / span 3;
    height: 100%;
}

.profile-icon {
    width: 90px;
    height: 90px;
    margin: 0 auto 15px;
    border-radius: 50%;
    background-color: #b198cb;
    color: #321E48;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: bold;
}

.profile-card h2 {
    margin-bottom: 8px;
}

.profile-card p {
    font-size: 14px;
}

.student-id {
    margin-top: 15px;
    color: #ddd;
}

.information-card {
    background-color: #fcf4e2c8;
    border: 2px solid #5B3B7A;
    border-radius: 8px;
    padding: 20px;
}

.information-card h2 {
    color: #321E48;
    font-size: 21px;
    margin-bottom: 18px;
}

.information-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.information {
    background-color: white;
    padding: 13px;
    border-radius: 5px;
    border-left: 4px solid #5B3B7A;
}

.information span {
    color: #5B3B7A;
    font-size: 13px;
    font-weight: bold;
}

.information p {
    margin-top: 6px;
    font-size: 14px;
}

.address {
    background-color: white;
    padding: 15px;
    border-left: 4px solid #5B3B7A;
    line-height: 1.7;
}

/* Header layout */
#header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    flex-wrap: wrap;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

#nav ul {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.theme-button {
    background: #5B3B7A;
    color: white;
    border: none;
    border-radius: 7px;
    padding: 9px 12px;
    font-size: 19px;
    cursor: pointer;
}

.theme-button:hover {
    background: #76549A;
}

/* Dark theme */
body.dark-theme {
    background-color: #18151F;
    color: #F1EDF5;
}
body.dark-theme .page-title{background-color: #292333;
    border-color: #76549A;
}
body.dark-theme .page-title h2{color: #D8C2F0;
}
body.dark-theme .page-title p {
    color: #F1EDF5;
}

body.dark-theme .information-card {
    background-color: #292333;
    border-color: #76549A;
}

body.dark-theme .information-card h2 {
    color: #D8C2F0;
}

body.dark-theme .information {
    background-color: #383044;
    border-left-color: #A98ACB;
}

body.dark-theme .information span {
    color: #D8C2F0;
}

body.dark-theme .information p {
    color: #F1EDF5;
}

body.dark-theme .address {
    background-color: #383044;
    border-left-color: #A98ACB;
    color: #F1EDF5;
}

body.dark-theme .profile-card {
    background-color: #292333;
    border: 1px solid #76549A;
}

body.dark-theme footer {
    background-color: #211A2B;
    color: #F1EDF5;
}

/* Responsive layout */
@media (max-width: 768px) {
    #header {
        flex-direction: column;
        padding: 15px;
    }

    .header-actions {
        width: 100%;
        justify-content: center;
        flex-wrap: wrap;
    }

    #nav ul {
        justify-content: center;
    }

    .profile-section {
        grid-template-columns: 1fr;
    }

    .profile-card {
        grid-row: auto;
    }

    .information-grid {
        grid-template-columns: 1fr;
    }
}

</style>
<body>

<div class="container">
    
<header id="header">
    <h1>STUDENT HUB PORTAL</h1>

    <div class="header-actions">
        <nav id="nav">
            <ul>
                <li><a href="home.html">Home</a></li>
                <li><a href="Dashboard.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="backend/logout.php">Logout</a></li>
            </ul>
        </nav>

        <button id="themeButton" class="theme-button"
                aria-label="Toggle dark mode"
                title="Toggle dark mode">🌙</button>
    </div>
</header>

    <section class="page-title">

        <h2>My Profile</h2>

        <p>
            View and manage your personal and academic information.
        </p>

    </section>

    <section class="profile-section">
        <div class="profile-card">

    <div class="profile-icon">
        <?= strtoupper(substr($student['name'], 0, 1)) ?>
    </div>

    <h2><?= escapeProfile($student['name']) ?></h2>

    <p>
        <?= escapeProfile($student['department'] ?: 'Student') ?>
        Student
    </p>

    <p class="student-id">
        Student ID: <?= escapeProfile($student['enrollment']) ?>
    </p>

</div>

        <div class="information-card">

            <h2>Personal Information</h2>

            <div class="information-grid">

                <div class="information">
                    <span>Full Name</span>
                    <p><?= escapeProfile($student['name']) ?></p>
                </div>

                <div class="information">
                    <span>Email</span>
                    <p><?= escapeProfile($student['email']) ?></p>
                </div>

                <div class="information">
                    <span>Phone</span>
                    <p><?= escapeProfile($student['phone'] ?: 'Not provided') ?></p>
                </div>

                <div class="information">
                    <span>Date of Birth</span>
                    <p>
                        <?php
                        echo !empty($student['dob'])
                            ? escapeProfile(date('d F Y', strtotime($student['dob'])))
                            : 'Not provided';
                        ?>
                    </p>
                </div>
            </div>

        </div>

        <div class="information-card academic">

            <h2>Academic Information</h2>

            <div class="information-grid">

                <div class="information">
                    <span>Student ID</span>
                    <p><?= escapeProfile($student['enrollment']) ?></p>
                </div>

                <div class="information">
                    <span>Department</span>
                    <p><?= escapeProfile($student['department'] ?: 'Not provided') ?></p>
                </div>

                <div class="information">
                    <span>Semester</span>
                    <p>
                        <?= !empty($student['semester'])
                            ? escapeProfile($student['semester']) . ' Semester'
                            : 'Not provided' ?>
                    </p>
                </div>

                <div class="information">
                    <span>Gender</span>
                    <p><?= escapeProfile($student['gender'] ?: 'Not provided') ?></p>
                </div>

            </div>

        </div>

        <div class="information-card">

            <h2>Address</h2>

                        <p class="address">
                <?= !empty($student['address'])
                    ? nl2br(escapeProfile($student['address']))
                    : 'Address not provided' ?>
            </p>

        </div>


    </section>
    <footer>

        <h2>StudentHub</h2>

        <p>Your Smart Campus Companion</p>

        <hr>

        <p>Email : support@studenthub.com</p>

        <p>Phone : +91 98765 43210</p>

        <p>© 2026 StudentHub Portal. All Rights Reserved.</p>

    </footer>


</div>
<script src="script.js"></script>
</body>

</html>