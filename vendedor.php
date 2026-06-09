<?php
require 'core/db.php';
require 'core/auth.php';
require 'controllers/VentasController.php';

$carrito = $_SESSION['carrito'] ?? [];

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'vendedor') {
    session_destroy();
    header("Location: login.php");
    exit();
}


$tiempo_inactivo = 1800;

if (isset($_SESSION['ultima_actividad'])) {

    $tiempo_transcurrido = time() - $_SESSION['ultima_actividad'];

    if ($tiempo_transcurrido > $tiempo_inactivo) {

        session_unset();
        session_destroy();

        header("Location: login.php?mensaje=sesion_expirada");
        exit();
    }
}

$_SESSION['ultima_actividad'] = time();

$seccionesPermitidas = [
    'inicio',
    'ventas',
    'nota_credito',
    'devolucion',
    'politicas_devolucion',
    'inventario',
    'registro_productos',
    'registro_clientes',
    'movimiento_stock',
    'actualizar_productos'
];

$seccion = $_GET['secciones_vendedor'] ?? 'inicio';

if(!in_array($seccion, $seccionesPermitidas)){
    $seccion = 'inicio';
}


$archivo = "secciones_vendedor/$seccion.php";
if ($seccion === 'actualizar_productos') {
    require_once 'controllers/ProductoController.php';
    $productos = ProductoController::obtenerProductos($conexion);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Vendedor</title>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="/auditoria/public/css_vendedor/css_notas_credito.css?v=<?= time() ?>">
</head>
<body>
<?php include 'vendedor/includes/menu_vendedor.php'; ?>

<div class="container mt-4">

<?php

if(file_exists($archivo)){
    include $archivo;
}else{
    echo "<div class='alert alert-warning'>
        <h4 class='alert-heading'>Elige una sección</h4>
          </div>";
}
?>

</div>
<script src="js_vendedor/devoluciones.js?v=<?= time() ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script src="js_vendedor/vendedor.js?v=<?= time() ?>"></script>
<script src="js_vendedor/helpers.js"></script>
<script src="js_vendedor/ui.js"></script>
    
<script src="js_vendedor/actualizar_producto.js"></script>
<script src="js_vendedor/productos.js"></script>

<script src="js_vendedor/inventario.js"></script>
<script src="js_vendedor/registro_producto.js"></script>
</body>
</html>