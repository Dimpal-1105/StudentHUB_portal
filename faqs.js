
const faqSearch = document.getElementById("faqSearch");
const faqCategory = document.getElementById("faqCategory");
const faqList = document.getElementById("faqList");
const faqMessage = document.getElementById("faqMessage");

let faqData = [];

async function loadFAQs() {
    try {
        const response = await fetch("faqs.json");

        if (!response.ok) {
            throw new Error("Unable to load faqs.json");
        }

        const data = await response.json();

        if (!Array.isArray(data)) {
            throw new Error("Invalid FAQ data format");
        }

        faqData = data;
        displayFAQs();

    } catch (error) {
        console.error("FAQ loading error:", error);

        faqMessage.textContent =
            "Unable to load FAQs. Please run StudentHUB using VS Code Live Server.";

        faqList.replaceChildren();
    }
}

function displayFAQs() {
    const searchText = faqSearch.value.trim().toLowerCase();
    const selectedCategory = faqCategory.value;

    const filteredFAQs = faqData.filter(faq => {
        const question = String(faq.question || "");
        const answer = String(faq.answer || "");
        const category = String(faq.category || "General");

        const matchesSearch =
            question.toLowerCase().includes(searchText) ||
            answer.toLowerCase().includes(searchText);

        const matchesCategory =
            selectedCategory === "All" ||
            category === selectedCategory;

        return matchesSearch && matchesCategory;
    });

    faqList.replaceChildren();

    if (filteredFAQs.length === 0) {
        faqMessage.textContent = "No matching questions found.";
        return;
    }

    faqMessage.textContent =
        `Showing ${filteredFAQs.length} question(s).`;

    filteredFAQs.forEach((faq, index) => {
        const item = document.createElement("article");
        item.className = "dynamic-faq-item";

        const questionButton = document.createElement("button");
        questionButton.className = "dynamic-faq-question";
        questionButton.type = "button";
        questionButton.id = `faq-question-${index}`;
        questionButton.setAttribute("aria-expanded", "false");

        const questionText = document.createElement("span");
        questionText.textContent = faq.question || "Untitled question";

        const icon = document.createElement("span");
        icon.className = "faq-toggle-icon";
        icon.textContent = "+";
        icon.setAttribute("aria-hidden", "true");

        questionButton.append(questionText, icon);

        const answer = document.createElement("div");
        answer.className = "dynamic-faq-answer";
        answer.id = `faq-answer-${index}`;
        answer.hidden = true;

        const answerText = document.createElement("p");
        answerText.textContent = faq.answer || "No answer available.";

        const categoryLabel = document.createElement("span");
        categoryLabel.className = "dynamic-faq-category";
        categoryLabel.textContent = faq.category || "General";

        answer.append(answerText, categoryLabel);

        questionButton.setAttribute("aria-controls", answer.id);

        questionButton.addEventListener("click", () => {
            const isExpanded =
                questionButton.getAttribute("aria-expanded") === "true";

            questionButton.setAttribute(
                "aria-expanded",
                String(!isExpanded)
            );

            answer.hidden = isExpanded;
            icon.textContent = isExpanded ? "+" : "−";
        });

        item.append(questionButton, answer);
        faqList.appendChild(item);
    });
}

faqSearch.addEventListener("input", displayFAQs);
faqCategory.addEventListener("change", displayFAQs);

loadFAQs();