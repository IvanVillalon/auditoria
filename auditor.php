<?php
session_start();
require 'core/db.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'auditor') {
    header("Location: login.php");
    exit();
}

include 'includes/menu_auditor.php';

$seccion = $_GET['seccion'] ?? 'inicio';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Auditor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">

<?php

$archivo = "secciones_auditor/$seccion.php";

if(file_exists($archivo)){
    include $archivo;
}else{
    echo "<div class='alert alert-warning'>
            La sección no existe
          </div>";
}
?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/auditor.js?v=<?= time() ?>"></script>

</body>
</html>