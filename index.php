<?php
session_start();
include("conexion.php");

// Inicializar variable de error
$error = '';

// Verificar sesión de administrador
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: panel.php');
    exit();
}

require_once "../login-lamp/config/APP.php";

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['login_error'] = "Error de seguridad. Por favor, recarga la página.";
    } else {
        // Validar y sanitizar datos
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['login_error'] = "Formato de email inválido";
        } else {
            // Consulta preparada para obtener el usuario
            $stmt = $conexion->prepare("SELECT id, email, password FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();

            // Verificar si se encontró el usuario
            if ($resultado->num_rows === 1) {
                $usuario = $resultado->fetch_assoc();
                
                // Verificar la contraseña
                if (password_verify($password, $usuario['password'])) {
                    // Iniciar sesión
                    $_SESSION['user_id'] = $usuario['id'];
                    $_SESSION['email'] = $usuario['email'];
                    $_SESSION['admin_logged_in'] = true;
                    
                    // Regenerar ID de sesión para prevenir fijación
                    session_regenerate_id(true);
                    
                    // Eliminar posibles errores de sesión
                    unset($_SESSION['login_error']);
                    
                    // Redirigir al panel
                    header("Location: panel.php");
                    exit();
                }
            }
            
            // Si llega aquí, las credenciales son incorrectas
            $_SESSION['login_error'] = "Credenciales incorrectas";
        }
    }
    
    // Redirigir para evitar reenvío del formulario
    header("Location: index.php");
    exit();
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Recuperar mensaje de error de sesión y eliminarlo
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | <?php echo COMPANY; ?></title>
  <link rel="stylesheet" href="<?php echo SERVERURL; ?>assets/styles/index.css">
</head>
<body>
  <div class="login-container">
    <div class="login-header">
      <h2>Inicio de Sesión</h2>
      <p>Accede a tu panel administrativo</p>
    </div>
    
    <div class="login-form">
      <?php if (!empty($error)): ?>
        <div class="error-message">
          <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>
      
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        
        <div class="form-group">
          <label for="email">Correo Electrónico</label>
          <input type="email" id="email" name="email" placeholder="tucorreo@ejemplo.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : '' ?>">
        </div>
        
        <div class="form-group">
          <label for="password">Contraseña</label>
          <div class="password-container">
            <input type="password" id="password" name="password" placeholder="••••••••" required>
            <span class="password-toggle" onclick="togglePassword()">
              👁️
            </span>
          </div>
        </div>
        
        <button type="submit" class="login-btn">
          INICIAR SESIÓN
        </button>
      </form>
      
      <div class="login-footer">
        <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
      </div>
      
      <div class="security-info">
        <p>Sistema protegido con las últimas medidas de seguridad</p>
        <div class="security-badge">
          <div class="badge">Proteccion XSS</div>
          <div class="badge">CSRF Tokens</div>
          <div class="badge">Hashing Bcrypt</div>
          <div class="badge">Prepared Statements</div>
        </div>
      </div>
    </div>
  </div>

  <script src="<?php echo SERVERURL; ?>assets/js/index.js" defer></script>
</body>
</html>