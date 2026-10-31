<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: Login.html');
    exit;
}

if (!isset($_SESSION['student_role']) ||
    $_SESSION['student_role'] !== 'admin') {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied | StudentHUB</title>
        <link rel="stylesheet" href="Style.css">
    </head>
    <body>
        <main style="max-width:600px; margin:60px auto; padding:30px; text-align:center; background:#fcf4e2; border:2px solid #5B3B7A; border-radius:10px;">
            <h2 style="color:#321E48;">Access Denied</h2>
            <p>You do not have permission to access the Admin page.</p>
            <a href="Dashboard.php">Return to Dashboard</a>
        </main>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHUB | Admin Dashboard</title>

    <link rel="stylesheet" href="Style.css">

    <style>
        .statistics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-top: 25px;
        }

        .stat-card {
            background-color: white;
            padding: 22px;
            text-align: center;
            border-radius: 8px;
            border-top: 5px solid #5B3B7A;
            box-shadow: 0 3px 10px rgba(50, 30, 72, 0.08);
        }

        .stat-card h2 {
            color: #321E48;
            font-size: 32px;
            margin-bottom: 8px;
        }

        .stat-card p {
            color: #555;
            font-size: 14px;
        }

        .management {
            margin-top: 30px;
        }

        .management > h2,
        .activity > h2 {
            color: #321E48;
            font-size: 24px;
        }

        .section-description {
            margin-top: 6px;
            margin-bottom: 15px;
            font-size: 15px;
        }

        .management-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .management-card {
            background-color: #fcf4e2;
            border: 2px solid #5B3B7A;
            border-radius: 8px;
            padding: 20px;
        }

        .number {
            color: #5B3B7A;
            font-size: 14px;
            font-weight: bold;
        }

        .management-card h3 {
            color: #321E48;
            font-size: 20px;
            margin: 8px 0;
        }

        .management-card p {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 18px;
        }

        .management-info {
            background-color: white;
            border-radius: 6px;
            padding: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }

        .management-info strong {
            color: #5B3B7A;
            font-size: 22px;
        }

        .management-info span {
            font-size: 14px;
        }

        .management-link {
            display: inline-block;
            background-color: #321E48;
            color: white;
            text-decoration: none;
            padding: 9px 15px;
            border-radius: 6px;
            font-size: 14px;
            transition: background-color 0.2s ease;
        }

        .management-link:hover {
            background-color: #5B3B7A;
        }

        .activity {
            margin-top: 30px;
        }

        .activity > h2 {
            margin-bottom: 15px;
        }

        .activity-box {
            background-color: white;
            border-left: 6px solid #5B3B7A;
            border-radius: 8px;
            padding: 5px 20px;
        }

        .activity-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item h3 {
            color: #321E48;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .activity-item p {
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .activity-item span {
            color: #5B3B7A;
            font-size: 13px;
            white-space: nowrap;
        }

        .dashboard-message {
            display: none;
            margin-top: 15px;
            padding: 12px 15px;
            background: white;
            border-radius: 6px;
            font-size: 14px;
        }

        .empty-activity {
            padding: 20px 0;
            color: #555;
            font-size: 14px;
        }

        @media (max-width: 700px) {
            .statistics {
                grid-template-columns: 1fr;
            }

            .management-grid {
                grid-template-columns: 1fr;
            }

            .activity-item {
                align-items: flex-start;
                flex-direction: column;
                gap: 6px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <!-- HEADER -->
    <header id="header">

        <h1>STUDENT HUB PORTAL</h1>
        <p>Administrator Panel</p>

        <nav id="nav">
            <ul>
                <li><a href="home.html">Home</a></li>
                <li><a href="Dashboard.php">Dashboard</a></li>
                <li><a href="students.php">Student Management</a></li>
                <li><a href="manage_events.php">Event Management</a></li>
                <li><a href="backend/logout.php">Logout</a></li>
            </ul>
        </nav>

    </header>

    <!-- PAGE TITLE -->
    <section class="page-title">

        <h2>Admin Dashboard</h2>

        <p>
            Monitor student records, campus events and registration
            activity from your administration dashboard.
        </p>

    </section>

    <!-- LIVE STATISTICS -->
    <section class="statistics">

        <div class="stat-card">
            <h2 id="totalStudents">—</h2>
            <p>Total Students</p>
        </div>

        <div class="stat-card">
            <h2 id="totalEvents">—</h2>
            <p>Total Events</p>
        </div>

        <div class="stat-card">
            <h2 id="totalRegistrations">—</h2>
            <p>Total Registrations</p>
        </div>

    </section>

    <div id="dashboardMessage"
         class="dashboard-message"
         role="status"
         aria-live="polite"></div>

    <!-- PORTAL MANAGEMENT -->
    <section class="management">

        <h2>Portal Management</h2>

        <p class="section-description">
            Access the main areas of the StudentHUB administration portal.
        </p>

        <div class="management-grid">

            <div class="management-card">

                <div class="number">01</div>

                <h3>Student Management</h3>

                <p>
                    View, search, add, edit and delete student records
                    and maintain student academic information.
                </p>

                <div class="management-info">
                    <strong id="managementStudentCount">—</strong>
                    <span>Registered Students</span>
                </div>

                <a href="students.php" class="management-link">
                    Manage Students
                </a>

            </div>

            <div class="management-card">

                <div class="number">02</div>

                <h3>Event Management</h3>

                <p>
                    Create, view, update and delete campus events
                    and manage their event information.
                </p>

                <div class="management-info">
                    <strong id="managementEventCount">—</strong>
                    <span>Available Events</span>
                </div>

                <a href="manage_events.php" class="management-link">
                    Manage Events
                </a>

            </div>

        </div>

    </section>

    <!-- LATEST REGISTRATIONS -->
    <section class="activity">

        <h2>Latest Registrations</h2>

        <div class="activity-box" id="latestRegistrations">

            <p class="empty-activity">Loading recent registrations...</p>

        </div>

    </section>

    <!-- FOOTER -->
    <footer>

        <h2>StudentHub</h2>

        <p>Your Smart Campus Companion</p>

        <hr>

        <p>Email: support@studenthub.com</p>

        <p>Phone: +91 98765 43210</p>

        <p>© 2026 StudentHub Portal. All Rights Reserved.</p>

    </footer>

</div>

<script>
    const statsUrl = "backend/admin_stats.php";

    const dashboardMessage =
        document.getElementById("dashboardMessage");

    function escapeHTML(value) {
        return String(value ?? "").replace(/[&<>"']/g, function (character) {
            return {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#39;"
            }[character];
        });
    }

    function showDashboardMessage(message, isError = false) {
        dashboardMessage.textContent = message;
        dashboardMessage.style.display = "block";
        dashboardMessage.style.color = isError ? "#b00020" : "#24733b";
    }

    function formatDate(value) {
        if (!value) {
            return "Date unavailable";
        }

        const normalizedValue = value.replace(" ", "T");
        const date = new Date(normalizedValue);

        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return date.toLocaleString();
    }

    async function loadDashboardStatistics() {
        try {
            const response = await fetch(statsUrl, {
                method: "GET",
                headers: {
                    "Accept": "application/json"
                },
                cache: "no-store"
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || "Unable to load dashboard statistics."
                );
            }

            const stats = result.statistics;

            document.getElementById("totalStudents").textContent =
                stats.total_students;

            document.getElementById("totalEvents").textContent =
                stats.total_events;

            document.getElementById("totalRegistrations").textContent =
                stats.total_registrations;

            document.getElementById("managementStudentCount").textContent =
                stats.total_students;

            document.getElementById("managementEventCount").textContent =
                stats.total_events;

            renderLatestRegistrations(result.latest_registrations);

        } catch (error) {
            showDashboardMessage(
                error.message || "Could not load dashboard data.",
                true
            );

            document.getElementById("latestRegistrations").innerHTML =
                '<p class="empty-activity">' +
                'Recent registrations could not be loaded.</p>';
        }
    }

    function renderLatestRegistrations(registrations) {
        const container =
            document.getElementById("latestRegistrations");

        if (!Array.isArray(registrations) || registrations.length === 0) {
            container.innerHTML =
                '<p class="empty-activity">' +
                'No registrations are available yet.</p>';
            return;
        }

        container.innerHTML = registrations.map(function (registration) {
            return `
                <div class="activity-item">
                    <div>
                        <h3>${escapeHTML(registration.student_name)}</h3>
                        <p>
                            ${escapeHTML(registration.student_email)}
                            registered for
                            ${escapeHTML(registration.event_title)}
                        </p>
                    </div>
                    <span>${escapeHTML(
                        formatDate(registration.registered_at)
                    )}</span>
                </div>
            `;
        }).join("");
    }

    loadDashboardStatistics();
</script>

<script src="script.js"></script>

</body>
</html>