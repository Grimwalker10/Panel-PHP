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

// Validar y sanitizar datos
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$name = trim($name); // Eliminar espacios en blanco al inicio y final
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validar nombre
if (strlen($name) < 2) {
    $_SESSION['create_error'] = "El nombre debe tener al menos 2 caracteres";
    header("Location: usuarios.php");
    exit();
}

if (strlen($name) > 50) {
    $_SESSION['create_error'] = "El nombre no puede tener más de 50 caracteres";
    header("Location: usuarios.php");
    exit();
}

// Validar caracteres permitidos: letras, espacios, apóstrofes, guiones y caracteres especiales en español
if (!preg_match('/^[\p{L}\s\'-]+$/u', $name)) {
    $_SESSION['create_error'] = "El nombre solo puede contener letras, espacios, apóstrofes (') y guiones (-)";
    header("Location: usuarios.php");
    exit();
}

// Validar que no comience o termine con caracteres no permitidos
if (preg_match('/^[\'-]/', $name) || preg_match('/[\'-]$/', $name)) {
    $_SESSION['create_error'] = "El nombre no puede comenzar o terminar con apóstrofe o guión";
    header("Location: usuarios.php");
    exit();
}

// Validar secuencias inválidas
if (strpos($name, "--") !== false || strpos($name, "''") !== false || 
    strpos($name, " -") !== false || strpos($name, "- ") !== false ||
    strpos($name, "' ") !== false || strpos($name, " '") !== false) {
    $_SESSION['create_error'] = "El nombre contiene secuencias inválidas de caracteres";
    header("Location: usuarios.php");
    exit();
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['create_error'] = "Formato de email inválido";
    header("Location: usuarios.php");
    exit();
}

// Verificar que las contraseñas coincidan
if ($password !== $confirm_password) {
    $_SESSION['create_error'] = "Las contraseñas no coinciden";
    header("Location: usuarios.php");
    exit();
}

// Validar longitud de contraseña
if (strlen($password) < 8) {
    $_SESSION['create_error'] = "La contraseña debe tener al menos 8 caracteres";
    header("Location: usuarios.php");
    exit();
}

// Verificar si el email ya existe
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $_SESSION['create_error'] = "Este email ya está registrado";
    header("Location: usuarios.php");
    exit();
}
$stmt->close();

// Hashear la contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insertar nuevo usuario con consulta preparada (incluyendo el campo nombre)
$stmt = $conexion->prepare("INSERT INTO usuarios (email, nombre, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $name, $password_hash);

if ($stmt->execute()) {
    $_SESSION['create_success'] = "Usuario creado correctamente";
} else {
    $_SESSION['create_error'] = "Error al crear el usuario: " . $conexion->error;
}

$stmt->close();
header("Location: usuarios.php");
exit();
?>