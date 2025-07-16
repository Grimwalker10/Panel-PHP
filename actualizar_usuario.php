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
    header('HTTP/1.1 403 Forbidden');
    exit('Token CSRF inválido');
}

// Validar ID
if (!isset($_POST['id']) || !ctype_digit($_POST['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('ID inválido');
}

$id = (int)$_POST['id'];

// Validar y sanitizar nombre
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$nombre = trim($nombre); // Eliminar espacios en blanco al inicio y final

// Validar nombre
if (strlen($nombre) < 2) {
    $_SESSION['update_error'] = "El nombre debe tener al menos 2 caracteres";
    header("Location: editar_usuario.php?id=" . $id);
    exit();
}

if (strlen($nombre) > 50) {
    $_SESSION['update_error'] = "El nombre no puede tener más de 50 caracteres";
    header("Location: editar_usuario.php?id=" . $id);
    exit();
}

// Validar caracteres permitidos: letras, espacios, apóstrofes, guiones y caracteres especiales en español
if (!preg_match('/^[\p{L}\s\'-]+$/u', $nombre)) {
    $_SESSION['update_error'] = "El nombre solo puede contener letras, espacios, apóstrofes (') y guiones (-)";
    header("Location: editar_usuario.php?id=" . $id);
    exit();
}

// Validar que no comience o termine con caracteres no permitidos
if (preg_match('/^[\'-]/', $nombre) || preg_match('/[\'-]$/', $nombre)) {
    $_SESSION['update_error'] = "El nombre no puede comenzar o terminar con apóstrofe o guión";
    header("Location: editar_usuario.php?id=" . $id);
    exit();
}

// Validar secuencias inválidas
if (strpos($nombre, "--") !== false || strpos($nombre, "''") !== false || 
    strpos($nombre, " -") !== false || strpos($nombre, "- ") !== false ||
    strpos($nombre, "' ") !== false || strpos($nombre, " '") !== false) {
    $_SESSION['update_error'] = "El nombre contiene secuencias inválidas de caracteres";
    header("Location: editar_usuario.php?id=" . $id);
    exit();
}

// Validar email
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['update_error'] = "Formato de email inválido";
    header("Location: editar_usuario.php?id=" . $id);
    exit();
}

// Manejar contraseña
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Verificar si se proporcionó contraseña
if (!empty($password)) {
    // Validar coincidencia de contraseñas
    if ($password !== $confirm_password) {
        $_SESSION['update_error'] = "Las contraseñas no coinciden";
        header("Location: editar_usuario.php?id=" . $id);
        exit();
    }
    
    // Validar longitud de contraseña
    if (strlen($password) < 8) {
        $_SESSION['update_error'] = "La contraseña debe tener al menos 8 caracteres";
        header("Location: editar_usuario.php?id=" . $id);
        exit();
    }
    
    // Hashear la nueva contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Actualizar con contraseña y nombre
    $stmt = $conexion->prepare("UPDATE usuarios SET email = ?, nombre = ?, password = ? WHERE id = ?");
    $stmt->bind_param("sssi", $email, $nombre, $password_hash, $id);
} else {
    // Actualizar sin cambiar contraseña, pero con nombre
    $stmt = $conexion->prepare("UPDATE usuarios SET email = ?, nombre = ? WHERE id = ?");
    $stmt->bind_param("ssi", $email, $nombre, $id);
}

// Ejecutar la actualización
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $_SESSION['update_success'] = "Usuario actualizado correctamente";
    } else {
        $_SESSION['update_info'] = "No se realizaron cambios en el usuario";
    }
} else {
    $_SESSION['update_error'] = "Error al actualizar el usuario: " . $conexion->error;
}

$stmt->close();
header("Location: usuarios.php");
exit();
?>