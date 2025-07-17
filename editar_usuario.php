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

require_once "../login-lamp/config/APP.php";

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
  <title>Edición de Usuario | <?php echo COMPANY; ?></title>
  <link rel="stylesheet" href="<?php echo SERVERURL; ?>assets/styles/editar_usuario.css">
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
  
  <script src="<?php echo SERVERURL; ?>assets/js/editar_usuario.js" defer></script>
</body>
</html>