// Securely get elements
const eyeIcon = document.getElementById("eye");
const passwordField = document.getElementById("password");

if (eyeIcon && passwordField) {
  eyeIcon.addEventListener("click", () => {
    if (passwordField.type === "password") {
      passwordField.type = "text";
      eyeIcon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
      passwordField.type = "password";
      eyeIcon.classList.replace("fa-eye-slash", "fa-eye");
    }
  });
}

const dropdownButton = document.getElementById("dropdownButton");
const dropdownMenu = document.getElementById("dropdownMenu");

dropdownButton.addEventListener("click", (event) => {
    event.stopPropagation();
    dropdownMenu.classList.toggle("hidden");
});

document.addEventListener("click", (event) => {
    if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
        dropdownMenu.classList.add("hidden");
    }
});

