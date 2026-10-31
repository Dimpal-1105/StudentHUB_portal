<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: Login.html');
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>

    <title>StudentHub | Events</title>

    <link rel="stylesheet" href="Style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .hero {
    margin-top: 20px;
    background: #fcf4e2c8;
    border-left: 6px solid #321E48;
    padding: 20px;
    border-radius: 8px;
    display: grid;
    grid-template-columns: 3fr 1fr;
    gap: 20px;
    align-items: center;
}

.hero-text {
    width: 70%;
}

.hero-text h2 {
    color: #321E48;
    font-size: 26px;
    margin-bottom: 10px;
}

.hero-text p {
    font-size: 15px;
    line-height: 1.6;
}

.hero-date {
    min-width: 180px;
    background: #321E48;
    color: white;
    text-align: center;
    padding: 15px;
    border-radius: 8px;
}

.hero-date h3 {
    font-size: 24px;
}

.stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 20px;
}

.featured {
    margin-top: 30px;
}

.featured-heading {
    margin-bottom: 12px;
}

.featured-heading h2 {
    color: #321E48;
}

.featured-card {
    display: grid;
    grid-template-columns: 120px 1fr;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #5B3B7A;
}

.featured-left {
    width: 120px;
    background: #321E48;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.featured-left h1 {
    font-size: 42px;
}

.featured-left span {
    font-size: 18px;
}

.featured-right {
    padding: 20px;
}

.featured-right h2 {
    color: #321E48;
    margin-bottom: 10px;
}

.featured-right p {
    line-height: 1.6;
}

.event-info {
    margin: 15px 0;
    display: flex;
    gap: 25px;
    font-weight: bold;
    font-size: 15px;
}

.featured-right button {
    background: #5B3B7A;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 5px;
    cursor: pointer;
}

.featured-right button:hover {
    background: #321E48;
}
.controls {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin: 25px auto;
    max-width: 1000px;
}

.controls input,
.controls select {
    flex: 1;
    min-width: 180px;
    padding: 12px;
    border: 1px solid #B9A8C9;
    border-radius: 6px;
    font-size: 15px;
}

#eventList {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 18px;
    max-width: 1000px;
    margin: auto;
}

.event-card {
    background: #fcf4e2;
    border-left: 5px solid #5B3B7A;
    padding: 18px;
    border-radius: 8px;
    box-shadow: 0 3px 8px #00000015;
}

.event-card h3 {
    color: #321E48;
    margin-top: 0;
}

.category {
    display: inline-block;
    background: #E4D9EF;
    color: #321E48;
    padding: 5px 9px;
    border-radius: 12px;
    font-size: 12px;
}

#message {
    text-align: center;
    margin: 20px;
}

#pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin: 25px;
}

button {
    background: #321E48;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 10px 18px;
    cursor: pointer;
}

button:hover {
    background: #5B3B7A;
}

button:disabled {
    background: #888;
    cursor: not-allowed;
}
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}
/* Display the modal when JavaScript adds the show class */
.modal.show {
    display: block;
}
.modal-content {
    background-color: white;
    width: 50%;
    margin: 10% auto;
    padding: 25px;
    border-radius: 8px;
    position: relative;
}

.close {
    position: absolute;
    right: 20px;
    top: 10px;
    font-size: 28px;
    cursor: pointer;
}

    </style>
</head>
<body>

<div class="container">
    <header id="header">

        <h1>StudentHub</h1>
        <div id="nav">

            <ul>

                <li><a href="home.html">Home</a></li>

                <li><a href="Dashboard.php">Dashboard</a></li>

            </ul>

        </div>

    </header>
    <section class="hero">

        <div class="hero-text">

            <h2>Campus Events</h2>

            <p>

                Stay informed about technical workshops,
                cultural programs, sports activities and
                other exciting events happening around the campus.

            </p>

        </div>

        <div class="hero-date">

            <h3>November 2026</h3>

            <p>5 Upcoming Events</p>

        </div>

    </section>

    <section class="featured">

        <div class="featured-heading">

            <h2> Featured Event</h2>

        </div>

        <div class="featured-card">

            <div class="featured-left">

                <h1>05</h1>

                <span>NEV</span>

            </div>

            <div class="featured-right">

                <h2>Technical Workshop</h2>

                <p>

                    Learn Web Development, Artificial Intelligence,
                    GitHub and modern development practices through
                    practical hands-on sessions conducted by
                    experienced industry professionals.

                </p>

                <div class="event-info">

                    <span> 10:00 AM</span>

                    <span> Computer Lab</span>

                </div>

                <button onclick="openModal()">View Details</button>

            </div>

        </div>

    </section>
<div class="controls">
    <input type="text" id="searchInput"
           placeholder="Search events...">

    <select id="categoryFilter">
        <option value="All">All Categories</option>
        <option value="Technical">Technical</option>
        <option value="Sports">Sports</option>
        <option value="Cultural">Cultural</option>
    </select>

    <select id="sortOrder">
        <option value="asc">Date: Oldest First</option>
        <option value="desc">Date: Newest First</option>
        <option value="title">Title: A to Z</option>
    </select>
</div>

<div id="message">Loading events...</div>
<div id="eventList"></div>

<div id="pagination">
    <button id="prevBtn">Previous</button>
    <span id="pageInfo">Page 1</span>
    <button id="nextBtn">Next</button>
</div>
<!-- Featured Event Details Modal -->
<div id="eventModal" class="modal" role="dialog"
     aria-modal="true" aria-labelledby="modalTitle">

    <div class="modal-content">
        <button type="button" class="close"
                onclick="closeModal()" aria-label="Close modal">
            &times;
        </button>

        <h2 id="modalTitle">Technical Workshop</h2>

        <p>
            Join our hands-on Technical Workshop to explore Web
            Development, Artificial Intelligence, GitHub, and modern
            development practices.
        </p>

        <p><strong>Date:</strong> 5 November 2026</p>
        <p><strong>Time:</strong> 10:00 AM</p>
        <p><strong>Location:</strong> Computer Lab</p>
        <p><strong>Category:</strong> Technical</p>

        <button type="button" onclick="closeModal()">Close</button>
    </div>

</div>
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
<script src="practical6.js"></script>
</body>

</html>