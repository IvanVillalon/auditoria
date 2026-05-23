<?php
include "conexion.php";

$id_producto = $_GET['id_producto'];
$id_bodega_central = 1; // ID de la Bodega Central

$stmt = $conexion->prepare("
    SELECT color, stock
    FROM stock_sucursal
    WHERE id_producto = ? AND id_sucursal = ? AND stock > 0
");
$stmt->bind_param("ii", $id_producto, $id_bodega_central);
$stmt->execute();
$resultado = $stmt->get_result();

$colores = [];
while ($fila = $resultado->fetch_assoc()) {
    $colores[] = [
        'color' => $fila['color'],
        'stock' => $fila['stock']
    ];
}

echo json_encode($colores);
?>