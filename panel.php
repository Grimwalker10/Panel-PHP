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
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel | Sistema Administrativo</title>
  <style>
    :root {
      --primary: #3498db;
      --secondary: #2c3e50;
      --success: #2ecc71;
      --danger: #e74c3c;
      --light: #ecf0f1;
      --dark: #34495e;
      --gradient-start: #83a4d4;
      --gradient-end: #b6fbff;
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
      padding: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 40px 20px;
    }
    
    .header {
      width: 100%;
      max-width: 800px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    
    .user-info {
      background-color: rgba(255, 255, 255, 0.7);
      padding: 10px 15px;
      border-radius: 30px;
      display: flex;
      align-items: center;
      backdrop-filter: blur(5px);
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    
    .user-icon {
      width: 32px;
      height: 32px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      margin-right: 10px;
    }
    
    .logout-btn {
      background: linear-gradient(135deg, var(--danger), #c0392b);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 30px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    
    .logout-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    }
    
    .panel {
      background-color: rgba(255, 255, 255, 0.85);
      padding: 40px;
      width: 100%;
      max-width: 800px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      backdrop-filter: blur(10px);
      text-align: center;
    }
    
    h1 {
      color: var(--secondary);
      margin-bottom: 15px;
      font-size: 2.5rem;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .welcome-text {
      color: #555;
      margin-bottom: 30px;
      font-size: 1.1rem;
      line-height: 1.6;
    }
    
    .dashboard {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      margin-top: 40px;
    }
    
    .dashboard-card {
      background: white;
      border-radius: 15px;
      padding: 25px;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }
    
    .dashboard-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 12px 20px rgba(0,0,0,0.15);
      border-color: var(--primary);
    }
    
    .card-icon {
      width: 70px;
      height: 70px;
      margin: 0 auto 20px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
    }
    
    .users-icon {
      background: linear-gradient(135deg, var(--primary), #1abc9c);
      color: white;
    }
    
    .stats-icon {
      background: linear-gradient(135deg, #9b59b6, var(--secondary));
      color: white;
    }
    
    .settings-icon {
      background: linear-gradient(135deg, #e67e22, #e74c3c);
      color: white;
    }
    
    .card-title {
      color: var(--dark);
      margin-bottom: 15px;
      font-size: 1.3rem;
    }
    
    .card-desc {
      color: #777;
      font-size: 0.95rem;
      margin-bottom: 20px;
      line-height: 1.5;
    }
    
    .card-btn {
      display: inline-block;
      padding: 10px 25px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .card-btn:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
    }
    
    .security-info {
      margin-top: 40px;
      padding: 20px;
      background: rgba(236, 240, 241, 0.6);
      border-radius: 15px;
      text-align: left;
    }
    
    .security-info h3 {
      color: var(--secondary);
      margin-bottom: 15px;
      display: flex;
      align-items: center;
    }
    
    .security-info ul {
      padding-left: 20px;
      color: #555;
    }
    
    .security-info li {
      margin-bottom: 8px;
      line-height: 1.5;
    }
    
    .lock-icon {
      margin-right: 10px;
      color: var(--success);
    }
    
    @media (max-width: 600px) {
      .panel {
        padding: 25px 15px;
      }
      
      .header {
        flex-direction: column;
        gap: 15px;
      }
      
      .dashboard {
        grid-template-columns: 1fr;
      }
    }
  </style>
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
  
  <script>
    // Animación al cargar
    document.addEventListener('DOMContentLoaded', function() {
      const cards = document.querySelectorAll('.dashboard-card');
      cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
          card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
        }, 200 + (index * 150));
      });
    });
  </script>
</body>
</html>