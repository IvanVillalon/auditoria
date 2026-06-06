<?php

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../controllers/ProductoController.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "ID inválido"
    ]);
    exit;
}

try {

    $producto = ProductoController::obtenerProductoPorId(
        $conexion,
        $id
    );

    echo json_encode([
        "status" => "ok",
        "producto" => $producto
    ]);

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "mensaje" => $e->getMessage()
    ]);
}