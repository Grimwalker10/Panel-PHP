// Función para mostrar/ocultar contraseña
function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const toggleButton = passwordField.nextElementSibling;

    if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleButton.textContent = "🔒";
    } else {
        passwordField.type = "password";
        toggleButton.textContent = "👁️";
    }
}

// Efecto de carga
document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('.register-container').style.opacity = '0';
    setTimeout(() => {
        document.querySelector('.register-container').style.transition = 'opacity 0.5s ease';
        document.querySelector('.register-container').style.opacity = '1';
    }, 100);
});