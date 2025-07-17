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

require_once "../login-lamp/config/APP.php";

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
  <title>Registro | <?php echo COMPANY; ?></title>
  <link rel="stylesheet" href="<?php echo SERVERURL; ?>assets/styles/registro.css">
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

  <script src="<?php echo SERVERURL; ?>assets/js/registro.js" defer></script>
</body>
</html>