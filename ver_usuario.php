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
  <title>Detalles del Usuario</title>
  <style>
    :root {
      --primary: #3498db;
      --secondary: #2c3e50;
      --light: #ecf0f1;
      --dark: #34495e;
      --gradient-start: #f4c4f3;
      --gradient-end: #fc67fa;
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
    
    .card {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
      max-width: 500px;
      width: 100%;
      text-align: center;
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(10px);
    }
    
    .card::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 8px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
    }
    
    .user-icon {
      width: 100px;
      height: 100px;
      margin: 0 auto 25px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 48px;
      font-weight: bold;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    
    h2 {
      margin-bottom: 30px;
      color: var(--secondary);
      font-size: 28px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    
    .user-info {
      text-align: left;
      margin-bottom: 30px;
      background: rgba(236, 240, 241, 0.6);
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .info-row {
      display: flex;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .info-row:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }
    
    .info-label {
      font-weight: 600;
      color: var(--secondary);
      min-width: 160px;
    }
    
    .info-value {
      color: var(--dark);
      flex: 1;
    }
    
    .actions {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 25px;
      flex-wrap: wrap;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      padding: 12px 25px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .btn-back {
      background: linear-gradient(135deg, var(--secondary), #1a252f);
      color: white;
    }
    
    .btn-edit {
      background: linear-gradient(135deg, #f39c12, #e67e22);
      color: white;
    }
    
    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }
    
    @media (max-width: 480px) {
      .card {
        padding: 30px 20px;
      }
      
      .info-row {
        flex-direction: column;
        gap: 5px;
      }
      
      .actions {
        flex-direction: column;
        align-items: center;
      }
    }
  </style>
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
  
  <script>
    // Animación al cargar
    document.addEventListener('DOMContentLoaded', function() {
      const card = document.querySelector('.card');
      card.style.opacity = '0';
      card.style.transform = 'translateY(30px)';
      
      setTimeout(() => {
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }, 100);
    });
  </script>
</body>
</html>