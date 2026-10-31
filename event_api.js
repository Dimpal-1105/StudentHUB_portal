const API_URL = "backend/api/events.php";

let eventsData = [];
let csrfToken = "";

const eventTableBody = document.getElementById("eventsTableBody");
const eventForm = document.getElementById("eventApiForm");
const eventModal = document.getElementById("eventApiModal");
const eventMessage = document.getElementById("eventApiMessage");

function escapeHTML(value) {
    return String(value ?? "").replace(/[&<>"']/g, character => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;"
    })[character]);
}

function showMessage(message, isError = false) {
    eventMessage.textContent = message;
    eventMessage.style.color = isError ? "#b00020" : "#24733b";
    eventMessage.style.display = "block";
}

async function apiRequest(method = "GET", data = null) {
    console.log("apiRequest called with:", method, data);
    const options = {
        method,
        headers: {
            "Accept": "application/json"
        }
    };

    if (method !== "GET") {
        options.headers["Content-Type"] = "application/json";
        options.headers["X-CSRF-Token"] = csrfToken;
        options.body = JSON.stringify(data);
    }

    const response = await fetch(API_URL, options);
    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || "The request failed.");
    }

    return result;
}

/* Load events without reloading the page */
async function loadEvents() {
    try {
        const result = await apiRequest("GET");

        csrfToken = result.csrf_token;
        eventsData = result.events;

        renderEvents();
    } catch (error) {
        showMessage(error.message, true);
    }
}

/* Render event rows */
function renderEvents() {
    eventTableBody.innerHTML = "";

    if (eventsData.length === 0) {
        eventTableBody.innerHTML = `
            <tr>
                <td colspan="6">No events found.</td>
            </tr>
        `;
        return;
    }

    eventsData.forEach(event => {
        const row = document.createElement("tr");

        const formattedDate = event.event_date
            ? new Date(event.event_date.replace(" ", "T"))
                .toLocaleString()
            : "";

        row.innerHTML = `
            <td>${escapeHTML(event.title)}</td>
            <td>${escapeHTML(event.category)}</td>
            <td>${escapeHTML(formattedDate)}</td>
            <td>${escapeHTML(event.location)}</td>
            <td>
                ${event.poster
                    ? `<img src="${escapeHTML(event.poster)}"
                            alt="Event poster"
                            style="width:65px;height:45px;object-fit:cover;">`
                    : "No poster"}
            </td>
            <td>
                <button type="button"
                    class="api-edit-button"
                    data-id="${Number(event.id)}">
                    Edit
                </button>

                <button type="button"
                    class="api-delete-button"
                    data-id="${Number(event.id)}">
                    Delete
                </button>
            </td>
        `;

        eventTableBody.appendChild(row);
    });
}

/* Open the form for adding an event */
function openAddEventForm() {
    eventForm.reset();
    document.getElementById("apiEventId").value = "";
    document.getElementById("eventApiFormTitle").textContent = "Add Event";
    eventModal.style.display = "block";
}

/* Open the form with existing event details */
function openEditEventForm(eventId) {
    const event = eventsData.find(item => Number(item.id) === eventId);

    if (!event) {
        showMessage("Event not found.", true);
        return;
    }

    eventForm.reset();

    document.getElementById("apiEventId").value = event.id;
    document.getElementById("apiTitle").value = event.title;
    document.getElementById("apiDescription").value = event.description;
    document.getElementById("apiCategory").value = event.category;
    document.getElementById("apiLocation").value = event.location;

    const dateValue = event.event_date.replace(" ", "T").slice(0, 16);
    document.getElementById("apiEventDate").value = dateValue;

    document.getElementById("eventApiFormTitle").textContent = "Edit Event";
    eventModal.style.display = "block";
}

function closeEventForm() {
    eventModal.style.display = "none";
}

/* Add or update an event without reloading */
eventForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    const eventId = document.getElementById("apiEventId").value;

    const eventDetails = {
        title: document.getElementById("apiTitle").value.trim(),
        description: document.getElementById("apiDescription").value.trim(),
        category: document.getElementById("apiCategory").value,
        event_date: document.getElementById("apiEventDate").value,
        location: document.getElementById("apiLocation").value.trim()
    };

    try {
        const result = eventId
            ? await apiRequest("PUT", {
                id: Number(eventId),
                ...eventDetails
            })
            : await apiRequest("POST", eventDetails);

        closeEventForm();
        showMessage(result.message);
        await loadEvents();
    } catch (error) {
        showMessage(error.message, true);
    }
});

/* Handle Edit and Delete buttons */
eventTableBody.addEventListener("click", async function (event) {
    const editButton = event.target.closest(".api-edit-button");
    const deleteButton = event.target.closest(".api-delete-button");

    if (editButton) {
        openEditEventForm(Number(editButton.dataset.id));
        return;
    }

    if (deleteButton) {
        const eventId = Number(deleteButton.dataset.id);

        if (!confirm("Are you sure you want to delete this event?")) {
            return;
        }

        try {
            const result = await apiRequest("DELETE", {
                id: eventId
            });

            showMessage(result.message);
            await loadEvents();
        } catch (error) {
            showMessage(error.message, true);
        }
    }
});

/* Initial load */
loadEvents();