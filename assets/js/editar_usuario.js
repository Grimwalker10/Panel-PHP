// Función para mostrar/ocultar contraseña
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggleIcon = field.nextElementSibling;

    if (field.type === "password") {
        field.type = "text";
        toggleIcon.textContent = "🔒";
    } else {
        field.type = "password";
        toggleIcon.textContent = "👁️";
    }
}

// Función para validar el nombre
function validateName(name) {
    // Eliminar espacios al principio y al final
    name = name.trim();

    // Validar longitud
    if (name.length < 2) {
        return { valid: false, message: "El nombre debe tener al menos 2 caracteres" };
    }

    if (name.length > 50) {
        return { valid: false, message: "El nombre no puede tener más de 50 caracteres" };
    }

    // Validar caracteres permitidos: letras, espacios, apóstrofes, guiones y caracteres especiales en español
    const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s'-]+$/;
    if (!regex.test(name)) {
        return {
            valid: false,
            message: "El nombre solo puede contener letras, espacios, apóstrofes (') y guiones (-)"
        };
    }

    // Validar que no comience o termine con caracteres no permitidos
    if (name.startsWith("'") || name.startsWith("-") ||
        name.endsWith("'") || name.endsWith("-")) {
        return {
            valid: false,
            message: "El nombre no puede comenzar o terminar con apóstrofe o guión"
        };
    }

    // Validar que no tenga secuencias inválidas como "--" o "''"
    if (name.includes("--") || name.includes("''") || name.includes("'-") || name.includes("-'")) {
        return {
            valid: false,
            message: "El nombre contiene secuencias inválidas de caracteres"
        };
    }

    return { valid: true };
}

// Validación al enviar el formulario
document.getElementById('userForm').addEventListener('submit', function (e) {
    let hasErrors = false;

    // Validar nombre
    const nameInput = document.getElementById('name');
    const nameValue = nameInput.value;
    const nameValidation = validateName(nameValue);

    if (!nameValidation.valid) {
        e.preventDefault();
        const errorElement = document.getElementById('name-error');
        const errorText = document.getElementById('name-error-text');
        errorText.textContent = nameValidation.message;
        errorElement.style.display = 'block';
        nameInput.style.borderColor = 'var(--error)';
        hasErrors = true;
    } else {
        document.getElementById('name-error').style.display = 'none';
        nameInput.style.borderColor = '#ddd';
    }

    // Validar contraseña
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    // Solo validar si se ingresó alguna contraseña
    if (password || confirmPassword) {
        if (password !== confirmPassword) {
            e.preventDefault();
            if (!hasErrors) {
                alert('Las contraseñas no coinciden. Por favor, inténtalo de nuevo.');
            }
            hasErrors = true;
        }

        if (password.length > 0 && password.length < 8) {
            e.preventDefault();
            if (!hasErrors) {
                alert('La contraseña debe tener al menos 8 caracteres.');
            }
            hasErrors = true;
        }
    }

    if (hasErrors) {
        return false;
    }
});

// Validación en tiempo real para el nombre
document.getElementById('name').addEventListener('input', function () {
    const nameValue = this.value;
    const validation = validateName(nameValue);
    const errorElement = document.getElementById('name-error');
    const errorText = document.getElementById('name-error-text');

    if (validation.valid) {
        errorElement.style.display = 'none';
        this.style.borderColor = '#ddd';
    } else {
        errorText.textContent = validation.message;
        errorElement.style.display = 'block';
        this.style.borderColor = 'var(--error)';
    }
});

// Animación al cargar
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.form-container');
    form.style.opacity = '0';
    form.style.transform = 'translateY(30px)';

    setTimeout(() => {
        form.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        form.style.opacity = '1';
        form.style.transform = 'translateY(0)';
    }, 100);
});

