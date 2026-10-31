
// =====================================================
// STUDENTHUB: DYNAMIC NOTICES USING FETCH API
// =====================================================

const noticeList = document.getElementById("noticeList");
const noticeMessage = document.getElementById("noticeMessage");
const noticeSearch = document.getElementById("noticeSearch");
const noticeCategory = document.getElementById("noticeCategory");
const noticeSort = document.getElementById("noticeSort");
const noticePrev = document.getElementById("noticePrev");
const noticeNext = document.getElementById("noticeNext");
const noticePageInfo = document.getElementById("noticePageInfo");

let notices = [];
let noticeCurrentPage = 1;

const noticeItemsPerPage = 3;

// Fetch notice records from the external JSON file
async function loadNotices() {
    try {
        const response = await fetch("notices.json");

        if (!response.ok) {
            throw new Error("Unable to load notices.json");
        }

        const data = await response.json();

        if (!Array.isArray(data)) {
            throw new Error("Invalid notices data format");
        }

        notices = data;
        displayNotices();

    } catch (error) {
        console.error("Notices loading error:", error);

        noticeMessage.textContent =
            "Unable to load notices. Please open StudentHUB using VS Code Live Server.";

        noticeList.replaceChildren();
    }
}

// Display filtered, sorted and paginated notices
function displayNotices() {
    if (
        !noticeList ||
        !noticeMessage ||
        !noticeSearch ||
        !noticeCategory ||
        !noticeSort ||
        !noticePrev ||
        !noticeNext ||
        !noticePageInfo
    ) {
        return;
    }

    const searchText = noticeSearch.value.toLowerCase().trim();
    const selectedCategory = noticeCategory.value;
    const selectedSort = noticeSort.value;

    // Search by notice title and description
    let filteredNotices = notices.filter(notice => {
        const title = String(notice.title || "");
        const description = String(notice.description || "");

        const matchesSearch =
            title.toLowerCase().includes(searchText) ||
            description.toLowerCase().includes(searchText);

        const matchesCategory =
            selectedCategory === "All" ||
            notice.category === selectedCategory;

        return matchesSearch && matchesCategory;
    });

    // Sorting
    filteredNotices.sort((a, b) => {
        if (selectedSort === "newest") {
            return String(b.date).localeCompare(String(a.date));
        }

        if (selectedSort === "oldest") {
            return String(a.date).localeCompare(String(b.date));
        }

        if (selectedSort === "title") {
            return String(a.title).localeCompare(String(b.title));
        }

        return 0;
    });

    // Pagination
    const totalPages = Math.ceil(
        filteredNotices.length / noticeItemsPerPage
    );

    noticeCurrentPage = Math.min(
        noticeCurrentPage,
        Math.max(1, totalPages)
    );

    const startIndex =
        (noticeCurrentPage - 1) * noticeItemsPerPage;

    const pageNotices = filteredNotices.slice(
        startIndex,
        startIndex + noticeItemsPerPage
    );

    // Clear previous results
    noticeList.replaceChildren();

    if (pageNotices.length === 0) {
        noticeMessage.textContent = "No matching notices found.";
    } else {
        noticeMessage.textContent =
            `Showing ${pageNotices.length} of ${filteredNotices.length} notice(s).`;

        pageNotices.forEach(notice => {
            const card = document.createElement("article");
            card.className = "notice-card";

            const category = document.createElement("span");
            category.className = "notice-category";
            category.textContent = notice.category || "General";

            const title = document.createElement("h3");
            title.textContent = notice.title || "Untitled Notice";

            const date = document.createElement("p");
            date.className = "notice-date";

            const formattedDate = notice.date
                ? new Date(notice.date + "T00:00:00").toLocaleDateString(
                    "en-IN",
                    {
                        day: "2-digit",
                        month: "short",
                        year: "numeric"
                    }
                )
                : "Date not available";

            date.textContent = `📅 ${formattedDate}`;

            const description = document.createElement("p");
            description.className = "notice-description";
            description.textContent =
                notice.description || "No description available.";

            card.append(category, title, date, description);
            noticeList.appendChild(card);
        });
    }

    noticePageInfo.textContent =
        `Page ${noticeCurrentPage} of ${Math.max(1, totalPages)}`;

    noticePrev.disabled = noticeCurrentPage <= 1;
    noticeNext.disabled =
        totalPages === 0 || noticeCurrentPage >= totalPages;
}

// Search
noticeSearch.addEventListener("input", () => {
    noticeCurrentPage = 1;
    displayNotices();
});

// Filter
noticeCategory.addEventListener("change", () => {
    noticeCurrentPage = 1;
    displayNotices();
});

// Sort
noticeSort.addEventListener("change", () => {
    noticeCurrentPage = 1;
    displayNotices();
});

// Previous page
noticePrev.addEventListener("click", () => {
    if (noticeCurrentPage > 1) {
        noticeCurrentPage--;
        displayNotices();
    }
});

// Next page
noticeNext.addEventListener("click", () => {
    noticeCurrentPage++;
    displayNotices();
});

// Start loading the notices
loadNotices();