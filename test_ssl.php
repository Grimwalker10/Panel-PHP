<?php
include 'conexion.php';
$result = $conexion->query("SHOW STATUS LIKE 'Ssl_cipher'");
$row = $result->fetch_assoc();
echo "Cifrado SSL: " . ($row['Value'] ?: 'NO ACTIVO');
?>