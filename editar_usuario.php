<?php
session_start();
include("conexion.php");

// Verificar sesión de administrador
if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validar ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('ID inválido');
}

$id = (int)$_GET['id'];

// Consulta preparada segura
$stmt = $conexion->prepare("SELECT id, email, nombre, fecha_registro FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header('HTTP/1.1 404 Not Found');
    exit('Usuario no encontrado');
}

$usuario = $resultado->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edición de Usuario | Sistema Administrativo</title>
  <style>
    :root {
      --primary: #3498db;
      --secondary: #2c3e50;
      --success: #2ecc71;
      --warning: #f39c12;
      --error: #e74c3c;
      --light: #ecf0f1;
      --dark: #34495e;
      --gradient-start: #a27ef7ff;
      --gradient-end: #fd6bc0ff;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }
    
    .form-container {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
      max-width: 500px;
      width: 100%;
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(10px);
    }
    
    .form-container::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
    }
    
    h2 {
      text-align: center;
      margin-bottom: 30px;
      color: var(--secondary);
      font-size: 28px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    
    .form-group {
      margin-bottom: 25px;
      position: relative;
    }
    
    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--secondary);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .form-icon {
      font-size: 20px;
    }
    
    input {
      width: 100%;
      padding: 14px 18px;
      border: 1px solid #ddd;
      border-radius: 10px;
      font-size: 16px;
      transition: all 0.3s ease;
    }
    
    input:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
      transform: translateY(-2px);
    }
    
    .password-toggle {
      position: absolute;
      right: 15px;
      top: 42px;
      cursor: pointer;
      color: #777;
      font-size: 20px;
    }
    
    .btn-submit {
      background: linear-gradient(135deg, var(--success), #27ae60);
      color: white;
      padding: 15px;
      border: none;
      border-radius: 10px;
      font-size: 18px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
    }
    
    .btn-submit:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(39, 174, 96, 0.3);
    }
    
    .btn-back {
      display: inline-block;
      width: 100%;
      text-align: center;
      margin-top: 20px;
      color: var(--primary);
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    .btn-back:hover {
      color: var(--secondary);
      text-decoration: underline;
    }
    
    .info-box {
      background-color: rgba(236, 240, 241, 0.6);
      padding: 15px;
      border-radius: 10px;
      margin-top: 20px;
      font-size: 14px;
      color: #555;
    }
    
    .error-message {
      color: var(--error);
      font-size: 14px;
      margin-top: 5px;
      display: none;
    }
    
    .error-icon {
      color: var(--error);
      margin-right: 5px;
    }
    
    .validation-info {
      font-size: 13px;
      color: #666;
      margin-top: 5px;
      padding-left: 5px;
    }
    
    @media (max-width: 480px) {
      .form-container {
        padding: 30px 20px;
      }
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>✏️ Editar Usuario</h2>
    
    <form id="userForm" action="actualizar_usuario.php" method="POST">
      <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id'], ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
      
      <div class="form-group">
        <label for="id"><span class="form-icon">🆔</span> ID:</label>
        <input type="text" id="id" value="<?= htmlspecialchars($usuario['id'], ENT_QUOTES, 'UTF-8') ?>" disabled>
      </div>
      
      <div class="form-group">
        <label for="name"><span class="form-icon">👤</span> Nombre:</label>
        <input type="text" id="name" name="nombre" value="<?= htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8') ?>" required
               placeholder="Ej: María González">
        <div class="validation-info">Mínimo 2 caracteres, máximo 50. Solo letras, espacios y apóstrofes.</div>
        <div class="error-message" id="name-error">
          <span class="error-icon">❌</span>
          <span id="name-error-text"></span>
        </div>
      </div>

      <div class="form-group">
        <label for="email"><span class="form-icon">✉️</span> Email:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?>" required>
      </div>
      
      <div class="form-group">
        <label for="fecha_registro"><span class="form-icon">📅</span> Fecha de Registro:</label>
        <input type="text" id="fecha_registro" value="<?= htmlspecialchars($usuario['fecha_registro'], ENT_QUOTES, 'UTF-8') ?>" disabled>
      </div>
      
      <div class="form-group">
        <label for="password"><span class="form-icon">🔑</span> Nueva Contraseña:</label>
        <input type="password" id="password" name="password" placeholder="Dejar en blanco para no cambiar">
        <span class="password-toggle" onclick="togglePassword('password')">👁️</span>
      </div>
      
      <div class="form-group">
        <label for="confirm_password"><span class="form-icon">🔒</span> Confirmar Contraseña:</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite la nueva contraseña">
        <span class="password-toggle" onclick="togglePassword('confirm_password')">👁️</span>
      </div>
      
      <div class="info-box">
        <strong>Nota:</strong> Si no deseas cambiar la contraseña, deja los campos de contraseña en blanco.
      </div>
      
      <button type="submit" class="btn-submit">💾 Guardar Cambios</button>
    </form>
    
    <a href="usuarios.php" class="btn-back">← Volver a la lista de usuarios</a>
  </div>
  
  <script>
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
    document.getElementById('userForm').addEventListener('submit', function(e) {
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
    document.getElementById('name').addEventListener('input', function() {
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
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('.form-container');
      form.style.opacity = '0';
      form.style.transform = 'translateY(30px)';
      
      setTimeout(() => {
        form.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        form.style.opacity = '1';
        form.style.transform = 'translateY(0)';
      }, 100);
    });
  </script>
</body>
</html>