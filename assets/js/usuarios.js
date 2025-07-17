// Control del modal
const openModalBtn = document.getElementById('openModalBtn');
const modal = document.getElementById('createUserModal');
const closeModalBtn = document.querySelector('.close-modal');
const createForm = document.getElementById('createForm');

// Función para validar el nombre
function validateName(name) {
    // Eliminar espacios al principio y al final
    name = name.trim();

    // Validar longitud
    if (name.length < 2) {
        return {
            valid: false,
            message: "El nombre debe tener al menos 2 caracteres"
        };
    }

    if (name.length > 50) {
        return {
            valid: false,
            message: "El nombre no puede tener más de 50 caracteres"
        };
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

    return {
        valid: true
    };
}

// Validación del nombre en tiempo real
document.getElementById('name').addEventListener('input', function () {
    const nameValue = this.value;
    const validation = validateName(nameValue);
    const errorElement = document.getElementById('name-error');
    const errorText = document.getElementById('name-error-text');

    if (validation.valid) {
        errorElement.style.display = 'none';
        this.style.borderColor = '';
    } else {
        errorText.textContent = validation.message;
        errorElement.style.display = 'block';
        this.style.borderColor = '#e74c3c';
    }
});

// Abrir modal
openModalBtn.addEventListener('click', function () {
    modal.classList.add('visible');
    document.body.style.overflow = 'hidden'; // Prevenir scroll del fondo
});

// Cerrar modal al hacer clic en la X
closeModalBtn.addEventListener('click', function () {
    modal.classList.remove('visible');
    document.body.style.overflow = 'auto';
});

// Cerrar modal al hacer clic fuera del contenido
modal.addEventListener('click', function (e) {
    if (e.target === modal) {
        modal.classList.remove('visible');
        document.body.style.overflow = 'auto';
    }
});

// Cerrar modal con la tecla Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.classList.contains('visible')) {
        modal.classList.remove('visible');
        document.body.style.overflow = 'auto';
    }
});

// Validación de contraseña en el formulario
createForm.addEventListener('submit', function (e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Las contraseñas no coinciden. Por favor, inténtalo de nuevo.');
    }
});

// Animación al cargar
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';

        setTimeout(() => {
            row.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
        }, 100 + (index * 50));
    });
});

// Función para manejar la eliminación con animación
function confirmDelete(userId) {
    const message = "¿Estás seguro de eliminar este usuario?\nEsta acción no se puede deshacer.";

    if (confirm(message)) {
        // Marcar la fila como eliminando
        const row = document.getElementById(`row-${userId}`);
        if (row) {
            row.classList.add('deleting');
        }

        return true; // Permitir envío del formulario
    }
    return false; // Cancelar eliminación
}

// Animación al cargar
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';

        setTimeout(() => {
            row.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
        }, 100 + (index * 50));
    });
});