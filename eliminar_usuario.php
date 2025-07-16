<?php
session_start();
include("conexion.php");

// Verificar sesión de administrador
if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
}

// Verificar token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['delete_error'] = "Token CSRF inválido. Por favor, recarga la página e intenta nuevamente.";
    header("Location: usuarios.php");
    exit();
}

// Validar y sanitizar ID
if (!isset($_POST['id']) || !ctype_digit($_POST['id'])) {
    $_SESSION['delete_error'] = "ID de usuario inválido";
    header("Location: usuarios.php");
    exit();
}

$id = (int)$_POST['id'];

// Prevenir eliminación del usuario actual
if (isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
    $_SESSION['delete_error'] = "No puedes eliminar tu propia cuenta mientras estás conectado";
    header("Location: usuarios.php");
    exit();
}

try {
    // Eliminar el usuario
    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $_SESSION['delete_success'] = "Usuario eliminado correctamente";
    } else {
        $_SESSION['delete_error'] = "No se pudo eliminar el usuario. Puede que ya haya sido eliminado o no exista.";
    }
    
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    // Manejar errores específicos de MySQL
    $error_message = "Error en la base de datos: ";
    switch ($e->getCode()) {
        case 1451: // Foreign key constraint
            $error_message .= "No se puede eliminar el usuario porque tiene registros relacionados.";
            break;
        default:
            $error_message .= $e->getMessage();
    }
    
    $_SESSION['delete_error'] = $error_message;
} catch (Exception $e) {
    $_SESSION['delete_error'] = "Error inesperado al intentar eliminar el usuario";
}

header("Location: usuarios.php");
exit();
?>