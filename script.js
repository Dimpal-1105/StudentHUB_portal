
/* =========================
   COLLAPSIBLE FAQ
   ========================= */

const questions = document.querySelectorAll(".question");

questions.forEach(function(question) {
    question.addEventListener("click", function() {
        const answer = this.nextElementSibling;

        if (answer.style.display === "block") {
            answer.style.display = "none";
        } else {
            answer.style.display = "block";
        }
    });
});


/* =========================
   MODAL POPUP
   ========================= */

function openModal() {
    const modal = document.getElementById("eventModal");

    if (modal) {
        modal.style.display = "block";
    }
}

function closeModal() {
    const modal = document.getElementById("eventModal");

    if (modal) {
        modal.style.display = "none";
    }
}


/* =========================
   HAMBURGER MENU
   ========================= */

const menuButton = document.getElementById("menuButton");
const homeNav = document.getElementById("home-nav");
const sidebar = document.querySelector(".sidebar");

if (menuButton) {
    menuButton.addEventListener("click", function() {

        if (homeNav) {
            homeNav.classList.toggle("active");
        }

        if (sidebar) {
            sidebar.classList.toggle("active");
        }

    });
}


/* =========================
   LIGHT / DARK THEME
   ========================= */

const themeButton = document.getElementById("themeButton");

// Load the saved theme
if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-theme");

    if (themeButton) {
        themeButton.textContent = "☀️";
    }
}

if (themeButton) {
    themeButton.addEventListener("click", function() {

        document.body.classList.toggle("dark-theme");

        if (document.body.classList.contains("dark-theme")) {
            localStorage.setItem("theme", "dark");
            themeButton.textContent = "☀️";
        } else {
            localStorage.setItem("theme", "light");
            themeButton.textContent = "🌙";
        }

    });
}


/* =========================
   NOTIFICATION BANNER
   ========================= */

function closeNotification() {
    const notification = document.getElementById("notification");

    if (notification) {
        notification.style.display = "none";
    }
}
