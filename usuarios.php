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

require_once "../login-lamp/config/APP.php";

// Consulta segura con MySQLi
$stmt = $conexion->prepare("SELECT id, email, fecha_registro FROM usuarios");
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Usuarios | <?php echo COMPANY; ?></title>
  <link rel="stylesheet" href="<?php echo SERVERURL; ?>assets/styles/usuarios.css">
</head>

<body>
  <div class="header">
    <h1 class="header-title">Administración de Usuarios</h1>
    <a href="panel.php" class="btn btn-back">
      ← Volver al Panel
    </a>
  </div>

  <div class="container">
    <?php
    // Mostrar mensajes de éxito/error de creacion
    if (isset($_SESSION['create_success'])) {
      echo '<div class="alert success">' . htmlspecialchars($_SESSION['create_success'], ENT_QUOTES, 'UTF-8') . '</div>';
      unset($_SESSION['create_success']);
    }

    if (isset($_SESSION['create_error'])) {
      echo '<div class="alert error">' . htmlspecialchars($_SESSION['create_error'], ENT_QUOTES, 'UTF-8') . '</div>';
      unset($_SESSION['create_error']);
    }

    // Mostrar mensajes de éxito/error de actualizacion
    if (isset($_SESSION['update_success'])) {
      echo '<div class="alert success">' . htmlspecialchars($_SESSION['update_success'], ENT_QUOTES, 'UTF-8') . '</div>';
      unset($_SESSION['update_success']);
    }

    if (isset($_SESSION['update_info'])) {
      echo '<div class="alert info">' . htmlspecialchars($_SESSION['update_info'], ENT_QUOTES, 'UTF-8') . '</div>';
      unset($_SESSION['update_info']);
    }

    if (isset($_SESSION['update_error'])) {
      echo '<div class="alert error">' . htmlspecialchars($_SESSION['update_error'], ENT_QUOTES, 'UTF-8') . '</div>';
      unset($_SESSION['update_error']);
    }

    // Mostrar mensajes de éxito/error de borrado
    if (isset($_SESSION['delete_success'])) {
      echo '<div class="alert success">' . htmlspecialchars($_SESSION['delete_success'], ENT_QUOTES, 'UTF-8') . '</div>';
      unset($_SESSION['delete_success']);
    }

    if (isset($_SESSION['delete_error'])) {
      echo '<div class="alert error">' . htmlspecialchars($_SESSION['delete_error'], ENT_QUOTES, 'UTF-8') . '</div>';
      unset($_SESSION['delete_error']);
    }
    ?>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Fecha Registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $resultado->fetch_assoc()): ?>
            <tr id="row-<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>">
              <td><?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($row['fecha_registro'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="action-cell">
                <a href="ver_usuario.php?id=<?= urlencode($row['id']) ?>" class="btn btn-action btn-view">
                  👁️ Ver
                </a>
                <a href="editar_usuario.php?id=<?= urlencode($row['id']) ?>" class="btn btn-action btn-edit">
                  ✏️ Editar
                </a>
                <form method="POST" action="eliminar_usuario.php"
                  onsubmit="return confirmDelete(<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>);">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                  <button type="submit" class="btn btn-action btn-delete">
                    🗑️ Eliminar
                  </button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <div class="add-user-container">
      <button id="openModalBtn" class="btn btn-create">➕ Agregar Nuevo Usuario</button>
    </div>
  </div>

  <!-- Modal para crear nuevo usuario -->
  <div id="createUserModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Agregar Nuevo Usuario</h2>
        <button class="close-modal">&times;</button>
      </div>

      <form id="createForm" method="POST" action="crear_usuario.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
          <label for="name">Nombre:</label>
          <input type="text" id="name" name="name" required placeholder="Ej: María González">
          <div class="validation-info" style="font-size: 13px; color: #666; margin-top: 5px;">
            Mínimo 2 caracteres, máximo 50. Solo letras, espacios y apóstrofes.
          </div>
          <div class="error-message" id="name-error" style="color: #e74c3c; font-size: 14px; margin-top: 5px; display: none;">
            <span style="margin-right: 5px;">❌</span>
            <span id="name-error-text"></span>
          </div>
        </div>

        <div class="form-group">
          <label for="password">Contraseña:</label>
          <input type="password" id="password" name="password" required minlength="8">
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirmar Contraseña:</label>
          <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
        </div>

        <button type="submit" class="btn-submit">➕ Crear Usuario</button>
      </form>
    </div>
  </div>

  <script src="<?php echo SERVERURL; ?>assets/js/usuarios.js" defer></script>
</body>

</html>