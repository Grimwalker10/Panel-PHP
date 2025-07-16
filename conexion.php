<?php
$conexion = new mysqli('localhost', 'root', '', 'login_lamp');

if ($conexion->connect_errno) {
    error_log("Error de conexión: " . $conexion->connect_error);
    header('HTTP/1.1 500 Internal Server Error');
    exit('Error en el servidor');
}

$conexion->set_charset('utf8mb4');
?>
