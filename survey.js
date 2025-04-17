document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("surveyForm");
    const addQuestionBtn = document.getElementById("addQuestion");
    const questionsContainer = document.getElementById("questionsContainer");
    const message = document.getElementById("message");

    addQuestionBtn.addEventListener("click", function () {
        const input = document.createElement("input");
        input.type = "text";
        input.className = "question-input w-full p-2 border rounded mb-2";
        input.placeholder = "Enter a question";
        questionsContainer.appendChild(input);
    });

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const title = document.getElementById("title").value;
        const description = document.getElementById("description").value;
        const questions = Array.from(document.querySelectorAll(".question-input"))
            .map(input => input.value)
            .filter(q => q.trim() !== "");

        const surveyData = {
            title: title,
            description: description,
            questions: questions
        };

        fetch("http://localhost:8888/projectxyz/store_survey.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(surveyData)
        })
        .then(response => response.json())
        .then(data => {
            console.log("✅ Response from backend:", data);
            message.textContent = data.message;
            message.classList.remove("text-red-500");
            message.classList.add("text-green-500");
        })
        .catch(error => {
            console.error("❌ Fetch Error:", error);
            message.textContent = "Failed to send data to the backend!";
        });
    });
});
