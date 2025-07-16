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
  <title>Usuarios | Sistema Administrativo</title>
  <style>
    :root {
      --primary: #3498db;
      --secondary: #2c3e50;
      --danger: #e74c3c;
      --success: #2ecc71;
      --warning: #f39c12;
      --light: #ecf0f1;
      --dark: #34495e;
      --gradient-start: rgb(173, 214, 252);
      --gradient-end: rgb(103, 222, 252);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
      margin: 0;
      padding: 20px;
      min-height: 100vh;
      position: relative;
    }

    .header {
      max-width: 1200px;
      margin: 0 auto 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
    }

    .header-title {
      color: var(--secondary);
      font-size: 2rem;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      padding: 10px 20px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .btn-back {
      background: linear-gradient(135deg, var(--secondary), #1a252f);
      color: white;
    }

    .btn-create {
      background: linear-gradient(135deg, var(--success), #27ae60);
      color: white;
    }

    .btn-back:hover,
    .btn-create:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      background-color: rgba(255, 255, 255, 0.9);
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
      overflow: hidden;
      backdrop-filter: blur(10px);
      position: relative;
      z-index: 1;
    }

    .table-container {
      overflow-x: auto;
      padding: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 800px;
    }

    thead {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
    }

    th {
      padding: 15px 20px;
      text-align: left;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    tbody tr {
      border-bottom: 1px solid rgba(0, 0, 0, 0.1);
      transition: background-color 0.3s ease;
    }

    tbody tr:hover {
      background-color: rgba(236, 240, 241, 0.5);
    }

    td {
      padding: 15px 20px;
      color: var(--dark);
    }

    .action-cell {
      display: flex;
      gap: 10px;
      justify-content: center;
    }

    .btn-action {
      padding: 8px 15px;
      border-radius: 20px;
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }

    .btn-view {
      background: linear-gradient(135deg, var(--primary), #1abc9c);
      color: white;
    }

    .btn-edit {
      background: linear-gradient(135deg, var(--warning), #e67e22);
      color: white;
    }

    .btn-delete {
      background: linear-gradient(135deg, var(--danger), #c0392b);
      color: white;
    }

    .btn-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .security-banner {
      background: linear-gradient(135deg, var(--secondary), #1a252f);
      color: white;
      padding: 15px;
      border-radius: 10px;
      margin: 20px;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .security-icon {
      font-size: 2rem;
    }

    .security-text {
      flex: 1;
    }

    .security-text h3 {
      margin-bottom: 5px;
    }

    .alert {
      padding: 15px;
      margin: 0 20px 20px;
      border-radius: 8px;
      font-weight: 500;
      text-align: center;
      animation: fadeIn 0.5s ease;
    }

    .alert.success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert.info {
      background-color: #ecedd4ff;
      color: #565715ff;
      border: 1px solid #e5e6c3ff;
    }

    .alert.error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .deleting {
      opacity: 0.5;
      background-color: #ffeaea;
      transition: all 0.5s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Estilos para el modal */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.7);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .modal.visible {
      display: flex;
      opacity: 1;
    }

    .modal-content {
      background-color: white;
      border-radius: 15px;
      width: 90%;
      max-width: 500px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      transform: translateY(-50px);
      transition: transform 0.3s ease;
    }

    .modal.visible .modal-content {
      transform: translateY(0);
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 2px solid var(--primary);
    }

    .modal-title {
      color: var(--secondary);
      font-size: 1.5rem;
      font-weight: 600;
    }

    .close-modal {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: var(--danger);
      transition: transform 0.3s ease;
    }

    .close-modal:hover {
      transform: rotate(90deg);
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      color: var(--dark);
      font-weight: 500;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.3s;
    }

    input:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
    }

    .btn-submit {
      background: linear-gradient(135deg, var(--success), #27ae60);
      color: white;
      padding: 12px 25px;
      font-size: 1rem;
      margin-top: 10px;
      width: 100%;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-submit:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(39, 174, 96, 0.3);
    }

    /* Botón para abrir modal */
    .add-user-container {
      text-align: center;
      margin: 20px;
    }

    @media (max-width: 768px) {
      .header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
      }

      .action-cell {
        flex-direction: column;
        align-items: center;
      }

      .modal-content {
        width: 95%;
        padding: 20px;
      }
    }
  </style>
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

  <script>
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
    document.getElementById('name').addEventListener('input', function() {
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
    openModalBtn.addEventListener('click', function() {
      modal.classList.add('visible');
      document.body.style.overflow = 'hidden'; // Prevenir scroll del fondo
    });

    // Cerrar modal al hacer clic en la X
    closeModalBtn.addEventListener('click', function() {
      modal.classList.remove('visible');
      document.body.style.overflow = 'auto';
    });

    // Cerrar modal al hacer clic fuera del contenido
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        modal.classList.remove('visible');
        document.body.style.overflow = 'auto';
      }
    });

    // Cerrar modal con la tecla Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && modal.classList.contains('visible')) {
        modal.classList.remove('visible');
        document.body.style.overflow = 'auto';
      }
    });

    // Validación de contraseña en el formulario
    createForm.addEventListener('submit', function(e) {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;

      if (password !== confirmPassword) {
        e.preventDefault();
        alert('Las contraseñas no coinciden. Por favor, inténtalo de nuevo.');
      }
    });

    // Animación al cargar
    document.addEventListener('DOMContentLoaded', function() {
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
    document.addEventListener('DOMContentLoaded', function() {
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
  </script>
</body>

</html>