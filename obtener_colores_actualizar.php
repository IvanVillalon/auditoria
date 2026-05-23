<?php
require 'conexion.php';

$id_producto = $_GET['id_producto'] ?? 0;
$id_sucursal = 1; // bodega central

$stmt = $conexion->prepare("
    SELECT color
    FROM inventario
    WHERE id_producto = ? AND id_sucursal = ?
");
$stmt->bind_param("ii", $id_producto, $id_sucursal);
$stmt->execute();

$resultado = $stmt->get_result();

$colores = [];

while ($fila = $resultado->fetch_assoc()) {
    $colores[] = $fila;
}

header('Content-Type: application/json');
echo json_encode($colores);
?>