
const eventList = document.getElementById("eventList");
const message = document.getElementById("message");
const searchInput = document.getElementById("searchInput");
const categoryFilter = document.getElementById("categoryFilter");
const sortOrder = document.getElementById("sortOrder");
const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");
const pageInfo = document.getElementById("pageInfo");

let events = [];
let currentPage = 1;
const itemsPerPage = 3;

// Fetch external JSON data
fetch("backend/get_events.php")
    .then(response => {
        if (!response.ok) {
            throw new Error("Unable to load events.json");
        }
        return response.json();
    })
    .then(data => {
        events = data;
        displayEvents();
    })
    .catch(error => {
        message.textContent =
            "Error loading events. Please run the page using Live Server.";
        console.error(error);
    });

// Search, filter, sort and display events
function displayEvents() {
    const searchText = searchInput.value.toLowerCase().trim();
    const category = categoryFilter.value;
    const sort = sortOrder.value;

    let filteredEvents = events.filter(event => {
        const matchesSearch =
            event.title.toLowerCase().includes(searchText) ||
            event.location.toLowerCase().includes(searchText);

        const matchesCategory =
            category === "All" || event.category === category;

        return matchesSearch && matchesCategory;
    });

    if (sort === "asc") {
        filteredEvents.sort((a, b) => a.date.localeCompare(b.date));
    } else if (sort === "desc") {
        filteredEvents.sort((a, b) => b.date.localeCompare(a.date));
    } else if (sort === "title") {
        filteredEvents.sort((a, b) => a.title.localeCompare(b.title));
    }

    const totalPages = Math.ceil(filteredEvents.length / itemsPerPage);

    if (currentPage > totalPages) {
        currentPage = Math.max(1, totalPages);
    }

    const start = (currentPage - 1) * itemsPerPage;
    const pageEvents = filteredEvents.slice(start, start + itemsPerPage);

    eventList.innerHTML = "";

    if (pageEvents.length === 0) {
        message.textContent = "No matching events found.";
    } else {
        message.textContent = `Found ${filteredEvents.length} event(s).`;

        pageEvents.forEach(event => {
            eventList.innerHTML += `
                <div class="event-card">
                    <span class="category">${event.category}</span>
                    <h3>${event.title}</h3>
                    <p><strong>Date:</strong> ${event.date}</p>
                    <p><strong>Location:</strong> ${event.location}</p>
                </div>
            `;
        });
    }

    pageInfo.textContent =
        `Page ${currentPage} of ${Math.max(1, totalPages)}`;

    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled =
        currentPage >= totalPages || totalPages === 0;
}

// Run again when the user searches, filters or sorts
searchInput.addEventListener("input", () => {
    currentPage = 1;
    displayEvents();
});

categoryFilter.addEventListener("change", () => {
    currentPage = 1;
    displayEvents();
});

sortOrder.addEventListener("change", () => {
    currentPage = 1;
    displayEvents();
});

// Pagination buttons
prevBtn.addEventListener("click", () => {
    if (currentPage > 1) {
        currentPage--;
        displayEvents();
    }
});

nextBtn.addEventListener("click", () => {
    currentPage++;
    displayEvents();
});
