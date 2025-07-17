// Función para mostrar/ocultar contraseña
function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleButton = document.querySelector('.password-toggle');

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
    document.querySelector('.login-container').style.opacity = '0';
    setTimeout(() => {
        document.querySelector('.login-container').style.transition = 'opacity 0.5s ease';
        document.querySelector('.login-container').style.opacity = '1';
    }, 100);
});