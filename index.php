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
  <title>Login | Sistema Administrativo</title>
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
      --gradient-start: #84ff6bff;
      --gradient-end: #63b4ffff;
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
    
    .login-container {
      background: var(--card-bg);
      width: 100%;
      max-width: 420px;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
      overflow: hidden;
      position: relative;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.5);
    }
    
    .login-header {
      background: linear-gradient(90deg, var(--primary), var(--secondary));
      padding: 30px 0;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .login-header::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, var(--success), var(--warning));
    }
    
    .login-header h2 {
      color: white;
      font-size: 28px;
      margin-bottom: 10px;
      position: relative;
      z-index: 1;
    }
    
    .login-header p {
      color: rgba(255, 255, 255, 0.9);
      font-size: 16px;
      position: relative;
      z-index: 1;
    }
    
    .login-form {
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
    
    .login-btn {
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
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
      box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    }
    
    .login-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
    }
    
    .login-footer {
      margin-top: 25px;
      text-align: center;
      font-size: 14px;
      color: var(--text-secondary);
    }
    
    .login-footer a {
      color: var(--primary);
      text-decoration: none;
      transition: all 0.3s;
      font-weight: 600;
    }
    
    .login-footer a:hover {
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
    
    .security-info {
      background-color: rgba(236, 240, 241, 0.6);
      padding: 15px;
      border-radius: 10px;
      margin-top: 25px;
      font-size: 14px;
      color: var(--text-secondary);
      text-align: center;
    }
    
    .security-badge {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 10px;
      flex-wrap: wrap;
    }
    
    .badge {
      background: white;
      border-radius: 50px;
      padding: 5px 15px;
      font-size: 12px;
      font-weight: 600;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    @media (max-width: 480px) {
      .login-container {
        border-radius: 15px;
      }
      
      .login-form {
        padding: 25px 20px;
      }
    }
    
    /* Animación de entrada */
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
    
    .login-container {
      animation: slideIn 0.6s ease-out forwards;
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
    }
  </style>
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

  <script>
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
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelector('.login-container').style.opacity = '0';
      setTimeout(() => {
        document.querySelector('.login-container').style.transition = 'opacity 0.5s ease';
        document.querySelector('.login-container').style.opacity = '1';
      }, 100);
    });
  </script>
</body>
</html>