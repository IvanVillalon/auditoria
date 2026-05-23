<?php
session_start();
require '../conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$seccion = $_GET['seccion'] ?? 'dashboard';
$archivo = "../secciones_admin/$seccion.php";

if (!file_exists($archivo)) {
    $contenido = "<div class='alert alert-danger'>Sección no encontrada</div>";
} else {
    ob_start(); // 👈 captura HTML
    include $archivo;
    $contenido = ob_get_clean();
}

$titulo = "Panel Admin";

include "../layouts/admin_layout.php";