<?php
session_start();
include("conexion.php");

// Verificar sesión de administrador
if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
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
  <title>Detalles del Usuario | <?php echo COMPANY; ?></title>
  <link rel="stylesheet" href="<?php echo SERVERURL; ?>assets/styles/ver_usuario.css">
</head>
<body>
  <div class="card">
    <div class="user-icon">
      <?= substr($usuario['email'], 0, 1) ?>
    </div>
    
    <h2>Detalles del Usuario</h2>
    
    <div class="user-info">
      <div class="info-row">
        <span class="info-label">ID:</span>
        <span class="info-value"><?= htmlspecialchars($usuario['id'], ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Email:</span>
        <span class="info-value"><?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Nombre:</span>
        <span class="info-value"><?= htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Fecha de Registro:</span>
        <span class="info-value"><?= htmlspecialchars($usuario['fecha_registro'], ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    </div>
    
    <div class="actions">
      <a href="usuarios.php" class="btn btn-back">← Volver a la lista</a>
      <a href="editar_usuario.php?id=<?= urlencode($usuario['id']) ?>" class="btn btn-edit">✏️ Editar Usuario</a>
    </div>
  </div>
  
  <script src="<?php echo SERVERURL; ?>assets/js/ver_usuario.js" defer></script>
</body>
</html>