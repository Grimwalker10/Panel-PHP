<?php
session_start();
include("conexion.php");

// Verificar sesión de administrador
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: panel.php');
    exit();
}

// Recuperar mensajes de sesión
$error = $_SESSION['register_error'] ?? '';
$success = $_SESSION['register_success'] ?? '';
$old_email = $_SESSION['old_email'] ?? '';
$old_name = $_SESSION['old_name'] ?? '';

// Limpiar mensajes después de recuperarlos
unset($_SESSION['register_error']);
unset($_SESSION['register_success']);
unset($_SESSION['old_email']);
unset($_SESSION['old_name']);

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Procesar formulario solo si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['register_error'] = "Error de seguridad. Por favor, recarga la página.";
    } else {
        // Validar y sanitizar datos
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $name = trim($_POST['name']); // Limpiar espacios en blanco
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Guardar datos para repoblar formulario
        $_SESSION['old_email'] = $email;
        $_SESSION['old_name'] = $name;

        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['register_error'] = "Formato de email inválido";
        } elseif ($password !== $confirm_password) {
            $_SESSION['register_error'] = "Las contraseñas no coinciden";
        } elseif (strlen($password) < 8) {
            $_SESSION['register_error'] = "La contraseña debe tener al menos 8 caracteres";
        } else {
            // Validar el nombre
            if (strlen($name) < 2) {
                $_SESSION['register_error'] = "El nombre debe tener al menos 2 caracteres";
            } elseif (strlen($name) > 50) {
                $_SESSION['register_error'] = "El nombre no puede tener más de 50 caracteres";
            } elseif (!preg_match('/^[\p{L}\s\'-]+$/u', $name)) {
                $_SESSION['register_error'] = "El nombre solo puede contener letras, espacios, apóstrofes (') y guiones (-)";
            } elseif (preg_match('/^[\'-]/', $name) || preg_match('/[\'-]$/', $name)) {
                $_SESSION['register_error'] = "El nombre no puede comenzar o terminar con apóstrofe o guión";
            } elseif (strpos($name, "--") !== false || strpos($name, "''") !== false || 
                strpos($name, " -") !== false || strpos($name, "- ") !== false ||
                strpos($name, "' ") !== false || strpos($name, " '") !== false) {
                $_SESSION['register_error'] = "El nombre contiene secuencias inválidas de caracteres";
            } else {
                // Verificar si el email ya existe
                $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $resultado = $stmt->get_result();

                if ($resultado->num_rows > 0) {
                    $_SESSION['register_error'] = "Este email ya está registrado";
                } else {
                    // Hashear la contraseña
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    // Insertar nuevo usuario
                    $insert = $conexion->prepare("INSERT INTO usuarios (email, password, nombre) VALUES (?, ?, ?)");
                    $insert->bind_param("sss", $email, $password_hash, $name);

                    if ($insert->execute()) {
                        $_SESSION['register_success'] = "¡Registro exitoso! Ahora puedes iniciar sesión";
                        // Limpiar datos antiguos
                        unset($_SESSION['old_email']);
                        unset($_SESSION['old_name']);
                    } else {
                        $_SESSION['register_error'] = "Error al registrar el usuario: " . $conexion->error;
                    }
                }
            }
        }
    }
    
    // Redirigir para evitar reenvío del formulario
    header("Location: registro.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro | Sistema Administrativo</title>
  <style>
    :root {
      --primary: #3498db;
      --primary-dark: #2980b9;
      --secondary: #2c3e50;
      --light: #ecf0f1;
      --dark: #34495e;
      --success: #2ecc71;
      --warning: #f39c12;
      --error: #e74c3c;
      --gradient-start: #55fa71ff;
      --gradient-end: #5c8fffff;
      --card-bg: rgba(255, 255, 255, 0.95);
      --text-primary: #2c3e50;
      --text-secondary: #7f8c8d;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
      background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }
    
    .register-container {
      background: var(--card-bg);
      width: 100%;
      max-width: 450px;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
      overflow: hidden;
      position: relative;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.5);
      animation: slideIn 0.6s ease-out forwards;
    }
    
    .register-header {
      background: linear-gradient(90deg, var(--success), var(--primary));
      padding: 30px 0;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .register-header::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, var(--warning), var(--primary));
    }
    
    .register-header h2 {
      color: white;
      font-size: 28px;
      margin-bottom: 10px;
      position: relative;
      z-index: 1;
    }
    
    .register-header p {
      color: rgba(255, 255, 255, 0.9);
      font-size: 16px;
      position: relative;
      z-index: 1;
    }
    
    .register-form {
      padding: 30px;
    }
    
    .form-group {
      margin-bottom: 25px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--text-primary);
    }
    
    input {
      width: 100%;
      padding: 14px 18px;
      border: 1px solid #ddd;
      border-radius: 10px;
      font-size: 16px;
      transition: all 0.3s ease;
      color: var(--text-primary);
    }
    
    input:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
      transform: translateY(-2px);
    }
    
    .register-btn {
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
      display: block;
      box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
    }
    
    .register-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(39, 174, 96, 0.4);
    }
    
    .login-link {
      margin-top: 25px;
      text-align: center;
      font-size: 14px;
      color: var(--text-secondary);
    }
    
    .login-link a {
      color: var(--primary);
      text-decoration: none;
      transition: all 0.3s;
      font-weight: 600;
    }
    
    .login-link a:hover {
      color: var(--secondary);
      text-decoration: underline;
    }
    
    .error-message {
      background-color: rgba(231, 76, 60, 0.1);
      color: var(--error);
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-size: 15px;
      border-left: 4px solid var(--error);
      animation: fadeIn 0.5s ease;
    }
    
    .success-message {
      background-color: rgba(46, 204, 113, 0.1);
      color: var(--success);
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-size: 15px;
      border-left: 4px solid var(--success);
      animation: fadeIn 0.5s ease;
    }
        
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .password-container {
      position: relative;
    }
    
    .password-toggle {
      position: absolute;
      right: 15px;
      top: 14px;
      cursor: pointer;
      color: var(--text-secondary);
      font-size: 18px;
      user-select: none;
    }
  </style>
</head>
<body>
  <div class="register-container">
    <div class="register-header">
      <h2>Crear Cuenta</h2>
      <p>Regístrate para acceder al sistema</p>
    </div>
    
    <div class="register-form">
      <?php if (!empty($error)): ?>
        <div class="error-message">
          <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>
      
      <?php if (!empty($success)): ?>
        <div class="success-message">
          <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>
      
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        
        <div class="form-group">
          <label for="email">Correo Electrónico</label>
          <input type="email" id="email" name="email" placeholder="tucorreo@ejemplo.com" required value="<?= htmlspecialchars($old_email, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        
        <div class="form-group">
          <label for="name">Nombre Completo</label>
          <input type="text" id="name" name="name" placeholder="Ej: María González" required value="<?= htmlspecialchars($old_name, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        
        <div class="form-group">
          <label for="password">Contraseña</label>
          <div class="password-container">
            <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required>
            <span class="password-toggle" onclick="togglePassword('password')">
              👁️
            </span>
          </div>
        </div>
        
        <div class="form-group">
          <label for="confirm_password">Confirmar Contraseña</label>
          <div class="password-container">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite tu contraseña" required>
            <span class="password-toggle" onclick="togglePassword('confirm_password')">
              👁️
            </span>
          </div>
        </div>
        
        <button type="submit" class="register-btn">
          REGISTRARSE
        </button>
      </form>
      
      <div class="login-link">
        <p>¿Ya tienes una cuenta? <a href="index.php">Inicia sesión aquí</a></p>
      </div>
    </div>
  </div>

  <script>
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
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelector('.register-container').style.opacity = '0';
      setTimeout(() => {
        document.querySelector('.register-container').style.transition = 'opacity 0.5s ease';
        document.querySelector('.register-container').style.opacity = '1';
      }, 100);
    });
  </script>
</body>
</html>