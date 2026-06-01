<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "proyectotitulo";

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8");