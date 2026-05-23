<?php
session_start();
require("conexion.php");

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id_reporte = $data['id_reporte'] ?? null;
$accion = $data['accion'] ?? null;
$cantidad = $data['cantidad'] ?? null;
$respuesta = $data['respuesta'] ?? '';

if (!$id_reporte || !$accion || !$cantidad) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Datos incompletos"
    ]);
    exit;
}

/*
BUSCAR DETALLE RELACIONADO AL REPORTE
*/
$stmt = $conexion->prepare("
SELECT id.id_detalle, id.id_producto, id.color, id.id_sucursal
FROM reportes_auditor ra
INNER JOIN inventario_detalle id 
    ON id.id_detalle = ra.id_referencia
WHERE ra.id_reporte = ?
");

$stmt->bind_param("i", $id_reporte);
$stmt->execute();
$result = $stmt->get_result();
$producto = $result->fetch_assoc();

if (!$producto) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Reporte no encontrado"
    ]);
    exit;
}

$id_detalle = $producto['id_detalle'];
$id_producto = $producto['id_producto'];
$color = $producto['color'];
$id_sucursal = $producto['id_sucursal'];

if ($accion === 'aumentar_stock') {

    // actualizar conteo físico
    $sql = $conexion->prepare("
        UPDATE inventario_detalle 
        SET stock_fisico = ?
        WHERE id_detalle = ?
    ");

    $sql->bind_param("ii", $cantidad, $id_detalle);

    if ($sql->execute()) {

        // actualizar stock real sucursal
        $sqlStock = $conexion->prepare("
            UPDATE stock_sucursal
            SET stock = ?
            WHERE id_producto = ?
            AND color = ?
            AND id_sucursal = ?
        ");

        $sqlStock->bind_param("iisi", $cantidad, $id_producto, $color, $id_sucursal);
        $sqlStock->execute();

        // cerrar reporte
        $sql2 = $conexion->prepare("
            UPDATE reportes_auditor 
            SET estado = 'resuelto',
                tipo_respuesta = ?,
                cantidad = ?,
                respuesta = ?
            WHERE id_reporte = ?
        ");

        $sql2->bind_param("sisi", $accion, $cantidad, $respuesta, $id_reporte);
        $sql2->execute();

        echo json_encode([
            "status" => "ok",
            "mensaje" => "Stock actualizado correctamente"
        ]);

    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => $sql->error
        ]);
    }

    exit;
}

elseif ($accion === 'restar_stock') {

    echo json_encode([
        "status" => "ok",
        "mensaje" => "Falta implementar resta de stock"
    ]);

    exit;
}
?>