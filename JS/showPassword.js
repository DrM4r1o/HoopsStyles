let passwordText = document.getElementById("passwordText");
let password = document.getElementById("password");
let bShowPass = document.getElementById("showPass");

bShowPass.addEventListener("mousedown", () => {
    passwordText.textContent = password.textContent;
});



