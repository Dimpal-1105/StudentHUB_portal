<?php

session_start();

// Require an authenticated student account.
if (!isset($_SESSION['student_id'])) {
    header('Location: Login.html');
    exit;
}

// Expire inactive sessions after 30 minutes.
$timeout = 1800;

if (
    isset($_SESSION['last_activity']) &&
    time() - $_SESSION['last_activity'] > $timeout
) {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();

    header('Location: Login.html?timeout=1');
    exit;
}

$_SESSION['last_activity'] = time();
$studentName = htmlspecialchars(
    $_SESSION['student_name'] ?? 'Student',
    ENT_QUOTES,
    'UTF-8'
);

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard</title>
    <link rel="stylesheet" href="Style.css">
</head>
<style>
    .dashboard {
    display: flex;
    margin-top: 20px;
    gap: 20px;
}

.sidebar {
    width: 250px;
    background-color: #321E48;
    color: white;
    padding: 20px;
}

.sidebar h2 {
    text-align: center;
}

.sidebar ul {
    list-style: none;
    padding: 0;
}

.sidebar li {
    margin: 15px 0;
}

.sidebar a {
    color: white;
    text-decoration: none;
}

.sidebar a:hover {
    color: #CBDCEB;
}

.sidebar h3 {
    color: white;
    margin-top: 25px;
}

.sidebar button {
    width: 210px;
    background-color: #9771bc;
    color: white;
    font-weight: bold;
    border: none;
    padding: 12px 25px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 17px;
}

.dashboard-content {
    flex: 1;
}

.top-menu {
    background-color: #321E48;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.student-name {
    font-size: 18px;
    font-weight: bold;
}

.search input {
    width: 300px;
    padding: 10px 15px;
    border: none;
    border-radius: 20px;
    font-size: 15px;
    outline: none;
}

.account-menu {
    display: flex;
    gap: 20px;
}

.account-menu a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    padding: 8px 15px;
    border-radius: 5px;
}

.account-menu a:hover {
    background-color: #926eb6;
}

.welcome {
    border: 2px solid #5B3B7A;
    background-color: #fcf4e2c8;
    padding: 25px;
    border-radius: 10px;
}

.welcome h2 {
    color: #321E48;
}

.cards {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.card {
    background-color: white;
    padding: 20px;
    width: 25%;
    text-align: center;
    border-radius: 10px;
    border-top: 5px solid #5B3B7A;
}

.card h3 {
    color: #321E48;
}

.card h1 {
    color: #5B3B7A;
}

.info-section {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.box {
    border: 2px solid #5B3B7A;
    background-color: #fcf4e2c8;
    padding: 35px;
    width: 100%;
    border-radius: 10px;
}

.box h2 {
    color: #321E48;
}
/* =========================
   HAMBURGER MENU
   ========================= */

.menu-button {
    display: none;

    background-color: #5B3B7A;
    color: white;

    border: none;
    border-radius: 5px;

    padding: 8px 12px;

    font-size: 20px;
    cursor: pointer;
}

.menu-button:hover {
    background-color: #321E48;
}
/* =========================
   NOTIFICATION BANNER
   ========================= */

.notification {
    background-color: #fcf4e2c8;
    color: #321E48;

    border-left: 6px solid #321E48;

    padding: 15px 20px;

    margin-bottom: 20px;

    border-radius: 8px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 20px;
}

.notification strong {
    color: #321E48;
}

.notification button {
    background: none;
    border: none;

    color: #321E48;

    font-size: 25px;
    font-weight: bold;

    cursor: pointer;
}

.notification button:hover {
    color: #5B3B7A;
}
/* =========================
   RESPONSIVE HAMBURGER MENU
   ========================= */

.menu-button {
    display: none;
}

@media (max-width: 768px) {
    .dashboard {
        position: relative;
    }

    .menu-button {
        display: inline-block;
    }

    .sidebar {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        width: 250px;
        max-width: 85vw;
        box-sizing: border-box;
        min-height: 100%;
        z-index: 1000;
        box-shadow: 5px 5px 20px rgba(0, 0, 0, 0.25);
        border-radius: 8px;
    }

    .sidebar.active {
        display: block;
    }
}

    .dashboard-content {
        min-width: 0;
        width: 100%;
    }

    .top-menu {
        flex-wrap: wrap;
        gap: 10px;
    }

    .search {
        order: 3;
        width: 100%;
    }

    .search input {
        width: 100%;
        box-sizing: border-box;
    }

    .account-menu {
        margin-left: auto;
        flex-wrap: wrap;
        gap: 8px;
    }
}
</style>

<body>

<div class="profile">

</div>
<div class="container">
    <div id="header">

        <h1>Student Dashboard</h1>

    </div>

    <div class="dashboard">

        <div class="sidebar" id="student-sidebar">


            <h2>StudentHub</h2>


            <ul>
                <li><a href="Dashboard.php"><button>Dashboard</button></a></li>


                <h3>Academic</h3>

                <li><a href="attendance.html"><button>Attendance</button></a></li>

                <li><a href="assigments.html"><button>Assignments</button></a></li>

                <li><a href="materials.html"><button>Study Materials</button></a></li>

                <li><a href="table.html"><button>Time-table</button></a></li>

                <li><a href="grades.html"><button>Grades</button></a></li>


                <h3>Campus</h3>

                <li><a href="events.php"><button>Events</button></a></li>

                <li><a href="notices.html"><button>Notices</button></a></li>

            </ul>
        </div>

        <div class="dashboard-content">
            <div class="top-menu">

    <!-- Hamburger Menu -->
   <button
    id="menuButton"
    class="menu-button"
    aria-label="Toggle sidebar"
    aria-controls="student-sidebar"
    aria-expanded="false">
    ☰
</button>
    <div id="notification" class="notification">

    <div>
        <strong>Important Notice:</strong>
        Technical Workshop registrations are now open.
    </div>

    <button onclick="closeNotification()">×</button>

    </div>
    <div class="search">

        <input type="text" placeholder="Search...">

    </div>

    <div class="account-menu">

        <a href="profile.php">Profile</a>

        <a href="feedback.html">Feedback</a>

        <a href="backend/logout.php">Logout</a>

    </div>

</div>
            <div class="welcome">
                <h2>Welcome, <?= $studentName ?>!</h2>
                <p>
                    Manage your academic activities,
                    track your progress and stay updated
                    with campus information.
                </p>
            </div>
            <div class="cards">


                <div class="card">

                    <h3>Attendance</h3>

                    <h1>85%</h1>

                    <p>Overall Attendance</p>

                </div>



                <div class="card">

                    <h3>Assignments</h3>

                    <h1>5</h1>

                    <p>Pending Assignments</p>

                </div>



                <div class="card">

                    <h3>Grades</h3>

                    <h1>8.7</h1>

                    <p>Current CGPA</p>

                </div>



                <div class="card">

                    <h3>Events</h3>

                    <h1>3</h1>

                    <p>Upcoming Events</p>

                    

                </div>


            </div>

            <div class="info-section">

                <div class="box">

                    <h2>Upcoming Events</h2>

                    <ul>

                        <li>Technical Workshop - 10 August</li>

                        <li>Sports Day - 18 August</li>

                        <li>Cultural Fest - 25 August</li>


                    </ul>


                </div>

                <div class="box">


                    <h2>Recent Notices</h2>


                    <ul>

                        <li>Mid Semester Exam Schedule Released</li>

                        <li>New Study Material Uploaded</li>

                        <li>Assignment Deadline Extended</li>


                    </ul>


                </div>



            </div>



        </div>



    </div>
    <footer>


        <h2>StudentHub</h2>


        <p>
            Your Smart Campus Companion
        </p>


        <p>
            StudentHub helps students manage academic
            resources, activities and campus updates easily.
        </p>


        <hr>


        <p>
            Email : support@studenthub.com
        </p>


        <p>
            Phone : +91 98765 43210
        </p>


        <p>
            © 2026 StudentHub Portal. All Rights Reserved.
        </p>


    </footer>




</div>


<script src="script.js"></script>
</body>

</html>
