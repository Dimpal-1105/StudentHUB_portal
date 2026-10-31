
/* =========================
   COLLAPSIBLE FAQ
   ========================= */

document.querySelectorAll(".question").forEach(function (question) {
    question.setAttribute("role", "button");
    question.setAttribute("tabindex", "0");
    question.setAttribute("aria-expanded", "false");

    function toggleAnswer() {
        const answer = question.nextElementSibling;

        if (!answer || !answer.classList.contains("answer")) return;

        const isOpen = answer.classList.toggle("answer-open");

        answer.style.display = isOpen ? "block" : "none";
        question.setAttribute("aria-expanded", isOpen);
    }

    question.addEventListener("click", toggleAnswer);

    question.addEventListener("keydown", function (event) {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            toggleAnswer();
        }
    });
});


/* =========================
   MODAL POPUP
   ========================= */

const modal = document.getElementById("eventModal");
let previousFocus = null;

function openModal() {
    if (!modal) return;

    previousFocus = document.activeElement;
    modal.classList.add("show");

    const closeButton = modal.querySelector(".modal-close");
    if (closeButton) closeButton.focus();
}

function closeModal() {
    if (!modal) return;

    modal.classList.remove("show");

    if (previousFocus) previousFocus.focus();
}

if (modal) {
    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });
}


/* =========================
   HAMBURGER MENU
   Supports Home and Dashboard separately
   ========================= */

const menuButton = document.getElementById("menuButton");
const homeNav = document.getElementById("home-nav");
const dashboardSidebar = document.querySelector(".sidebar");

if (menuButton) {

    // HOME PAGE MENU
    if (homeNav) {
        menuButton.addEventListener("click", function (event) {
            event.stopPropagation();

            const isOpen = homeNav.classList.toggle("active");

            menuButton.setAttribute("aria-expanded", isOpen);
            menuButton.textContent = isOpen ? "✕" : "☰";
        });

        document.addEventListener("click", function (event) {
            if (
                !homeNav.contains(event.target) &&
                !menuButton.contains(event.target)
            ) {
                homeNav.classList.remove("active");
                menuButton.setAttribute("aria-expanded", "false");
                menuButton.textContent = "☰";
            }
        });

        homeNav.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                homeNav.classList.remove("active");
                menuButton.setAttribute("aria-expanded", "false");
                menuButton.textContent = "☰";
            });
        });
    }

    // DASHBOARD SIDEBAR MENU
    if (dashboardSidebar) {
        menuButton.addEventListener("click", function (event) {
            event.stopPropagation();

            const isOpen = dashboardSidebar.classList.toggle("active");

            menuButton.setAttribute("aria-expanded", isOpen);
            menuButton.textContent = isOpen ? "✕" : "☰";
        });

        document.addEventListener("click", function (event) {
            if (
                !dashboardSidebar.contains(event.target) &&
                !menuButton.contains(event.target)
            ) {
                dashboardSidebar.classList.remove("active");
                menuButton.setAttribute("aria-expanded", "false");
                menuButton.textContent = "☰";
            }
        });

        dashboardSidebar.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                dashboardSidebar.classList.remove("active");
                menuButton.setAttribute("aria-expanded", "false");
                menuButton.textContent = "☰";
            });
        });
    }
}

/* =========================
   LIGHT / DARK THEME
   ========================= */


const themeButton = document.getElementById("themeButton");

if (themeButton) {
    // Restore the previously selected theme
    const savedTheme = localStorage.getItem("theme");

    if (savedTheme === "dark") {
        document.body.classList.add("dark-theme");
        themeButton.textContent = "☀️";
    }

    themeButton.addEventListener("click", function () {
        document.body.classList.toggle("dark-theme");

        const isDark = document.body.classList.contains("dark-theme");

        themeButton.textContent = isDark ? "☀️" : "🌙";
        localStorage.setItem("theme", isDark ? "dark" : "light");
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



let currentBackgroundSlide = 0;

function showBackgroundSlide(index) {
    const slides = document.querySelectorAll(".background-slide");
    const dots = document.querySelectorAll(".background-dot");

    if (slides.length === 0) return;

    // Wrap around when reaching either end
    currentBackgroundSlide =
        (index + slides.length) % slides.length;

    slides.forEach((slide, i) => {
        slide.classList.toggle(
            "active",
            i === currentBackgroundSlide
        );
    });

    dots.forEach((dot, i) => {
        dot.classList.toggle(
            "active",
            i === currentBackgroundSlide
        );
    });
}

function changeBackgroundSlide(direction) {
    showBackgroundSlide(currentBackgroundSlide + direction);
}



/* =========================
   KEYBOARD SUPPORT
   ========================= */

document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && modal && modal.classList.contains("show")) {
        closeModal();
    }
});

const form = document.getElementById("registerForm");

const validators = {
    name: value =>
    /^[A-Za-z]+(?:[ '-][A-Za-z]+)*$/.test(value.trim())
        ? ""
        : "Enter a valid name using letters only.",

    enrollment: value =>
        /^[0-9]{2}[A-Z]{2}[0-9]{3,6}$/.test(value.trim())
            ? ""
            : "Example: 25CE063. Use 2 digits, 2 uppercase letters and 3–6 digits.",

    email: value =>
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim())
            ? ""
            : "Enter a valid email address.",

    phone: value =>
        /^[6-9][0-9]{9}$/.test(value.trim())
            ? ""
            : "Enter a valid 10-digit mobile number.",

    address: value =>
        value.trim().length >= 5
            ? ""
            : "Address must contain at least 5 characters.",

    password: value =>
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@$!%*?&])[A-Za-z0-9@$!%*?&]{8,20}$/.test(value)
            ? ""
            : "Use 8–20 characters with uppercase, lowercase, a number and a special character.",

    confirmPassword: value =>
        value === document.getElementById("password").value
            ? ""
            : "Passwords do not match."
};

function validateField(field) {
    const validator = validators[field.id];
    if (!validator) return true;

    const error = validator(field.value);
    const errorElement = document.getElementById(field.id + "Error");

    if (errorElement) {
        errorElement.textContent = error;
    }

    field.classList.toggle("invalid-field", Boolean(error));
    field.classList.toggle("valid-field", !error && field.value.trim() !== "");

    return !error;
}

// Validate when the user leaves a field.
// Validate every field when Register is clicked.
form.addEventListener("submit", function(event) {
    let isValid = true;

    Object.keys(validators).forEach(id => {
        const field = document.getElementById(id);

        if (field && !validateField(field)) {
            isValid = false;
        }
    });

    if (!isValid) {
        event.preventDefault();
        return;
    }

    // Do not prevent the default submission.
    // The browser will submit the form to process_registration.php.
});

function showPass(id) {
    document.getElementById(id).type = "text";
}

function hidePass(id) {
    document.getElementById(id).type = "password";
}

function validatePassword() {
    const password = document.getElementById("password").value;
    const error = document.getElementById("passwordError");

    if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@$!%*?&])[A-Za-z0-9@$!%*?&]{8,20}$/.test(password)) {
        error.textContent =
            "Use 8–20 characters with uppercase, lowercase, a number and a special character.";
        return false;
    }

    error.textContent = "";
    return true;
}

function validateConfirmPassword() {
    const password = document.getElementById("password").value;
    const confirm = document.getElementById("confirmPassword").value;
    const error = document.getElementById("confirmPasswordError");

    if (confirm !== password) {
        error.textContent = "Passwords do not match.";
        return false;
    }

    error.textContent = "";
    return true;
}
