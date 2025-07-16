<?php
session_start();

// Verificar token CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    isset($_POST['csrf_token']) && 
    $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    
    // Destruir todas las variables de sesión
    $_SESSION = array();
    
    // Destruir la sesión
    session_destroy();
    
    // Redirigir a la página de login
    header("Location: index.php");
    exit();
} else {
    // Token inválido - manejar como error
    header('HTTP/1.1 403 Forbidden');
    exit('Token CSRF inválido');
}
?>