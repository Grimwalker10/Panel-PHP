<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once "../login-lamp/config/APP.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel | <?php echo COMPANY; ?></title>
  <link rel="stylesheet" href="<?php echo SERVERURL; ?>assets/styles/panel.css">
</head>
<body>
  <div class="header">
    <div class="user-info">
      <div class="user-icon"><?= substr($_SESSION['email'], 0, 1) ?></div>
      <span><?= htmlspecialchars($_SESSION['email'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    
    <form method="POST" action="logout.php">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="logout-btn">Cerrar Sesión</button>
    </form>
  </div>
  
  <div class="panel">
    <h1>Panel de Administración</h1>
    <p class="welcome-text">Acceso correcto. Bienvenido al centro de control de su aplicación.</p>
    
    <div class="dashboard">
      <div class="dashboard-card">
        <div class="card-icon users-icon">
          👥
        </div>
        <h3 class="card-title">Gestión de Usuarios</h3>
        <p class="card-desc">Administre todos los usuarios registrados en el sistema, edite información y roles.</p>
        <a href="usuarios.php" class="card-btn">Ver Usuarios</a>
      </div>
    
    <div class="security-info">
      <h3><span class="lock-icon">🔒</span> Medidas de Seguridad Activas</h3>
      <ul>
        <li><strong>Protección CSRF:</strong> Todos los formularios protegidos con tokens únicos</li>
        <li><strong>Encriptación:</strong> Datos sensibles cifrados en tránsito y en reposo</li>
        <li><strong>Monitoreo:</strong> Sistema detecta y alerta actividades sospechosas</li>
        <li><strong>Inyecciones SQL:</strong> Parches de seguridad aplicados para evitar injecciones</li>
      </ul>
    </div>
  </div>
  
  <script src="<?php echo SERVERURL; ?>assets/js/panel.js" defer></script>
</body>
</html>