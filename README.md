## StudentHUB Portal

StudentHUB is a web-based college student portal developed to simplify academic and campus-related activities.

### Project Repository
**GitHub:** [View StudentHUB Source Code](PASTE_YOUR_GITHUB_REPOSITORY_URL_HERE)

### Technologies Used
- HTML5
- CSS3
- JavaScript
- PHP
- MySQL
- Fetch API
- JSON

### Running the Project
The project can be run locally using XAMPP.

**Local URL:** `http://localhost/StudentHUB/`

### Key Features
- Student registration and login
- Role-based student and admin dashboards
- Student management (CRUD)
- Event management and poster uploads
- Event registration database
- Dynamic event loading using Fetch API
- PHP JSON API
- Admin dashboard with statistics


### Student Portal
- Student registration and login
- Session-based authentication
- Student dashboard
- Student profile
- Academic pages for attendance, assignments, grades and study materials
- Campus notices, events and FAQs
- Contact and feedback pages

### Admin Portal
- Admin authentication and role-based access
- Dashboard with dynamic statistics
- Student management: add, view, edit, delete and search
- Event management: add, view, edit and delete events
- Event poster upload
- Latest registration information

### Technical Features
- MySQL database connectivity using MySQLi
- Prepared statements for database operations
- Password hashing and password verification
- Server-side validation
- Session-based access control
- Fetch API for asynchronous event operations
- JSON data loading for selected portal modules
- Responsive page layouts

## Project Structure

StudentHUB/
├── backend/
│   ├── api/
│   │   └── events.php
│   ├── db.php
│   ├── process_registration.php
│   ├── process_login.php
│   ├── logout.php
│   ├── student_save.php
│   ├── student_delete.php
│   ├── event_save.php
│   ├── event_delete.php
│   └── admin_stats.php
├── data/
├── uploads/
│   └── event_posters/
├── home.html
├── Login.html
├── Register.html
├── Dashboard.php
├── Admin.php
├── students.php
├── manage_events.php
├── event_edit.php
├── events.php
├── profile.php
├── Style.css
├── script.js
├── event_api.js
└── studenthub.sql

Other HTML, JavaScript and JSON files support the portal's additional pages and practical requirements.

## Installation and Local Setup

### Requirements

- XAMPP
- PHP with MySQLi enabled
- MySQL/MariaDB
- A modern web browser

### Steps

1. Install and open XAMPP.
2. Start Apache and MySQL.
3. Copy the `StudentHUB` project folder into the XAMPP `htdocs` directory.
4. Open phpMyAdmin at `http://localhost/phpmyadmin`.
5. Create a database named `studenthub`.
6. Import the supplied `studenthub.sql` file into the database.
7. Open `backend/db.php` and verify the database connection settings:

   - Host: `localhost`
   - Username: `root`
   - Password: your local MySQL password
   - Database: `studenthub`

8. Ensure the `uploads/event_posters/` directory exists and is writable by the web server.
9. Open the application:

   `http://localhost/StudentHUB/home.html`

## Authentication

The portal supports separate student and administrator roles.

- Students can access student-facing pages after login.
- Administrators can access the administration dashboard and management modules.
- Passwords are stored using PHP password hashing.
- Protected PHP pages check the user's session and role.

To create an administrator account, register an account and assign the `admin` role through phpMyAdmin using an authorized local database account. Do not expose administrator credentials in the repository.

## Database

The application uses the `studenthub` MySQL database.

The database includes tables for student records, events and registrations. The SQL export supplied with the project is used to recreate the database during installation.

## Local Deployment

The application can be run locally using the XAMPP Apache server. The default local URL is:

`http://localhost/StudentHUB/`

An Apache virtual host can also be configured for a local development address such as `http://studenthub.local`.

## Security Notes

- Do not publish real student data or private credentials.
- Do not commit production passwords or secrets.
- Use prepared statements for database queries.
- Keep PHP error details out of public-facing pages.
- Validate uploaded files on the server.
- Use HTTPS and secure cookie settings if deploying beyond a local development environment.

## Project Purpose

This project demonstrates the integration of frontend development, PHP backend processing, MySQL database operations, authentication, security checks, asynchronous Fetch API functionality and local web deployment.