<?php

header('Content-Type: application/json');

$token = $_GET['token'] ?? '';

if ($token !== 'ERP2026') {
    http_response_code(401);

    echo json_encode([
        "status" => "error",
        "mensaje" => "No autorizado"
    ]);

    exit;
}

require("../conexion.php");

$sql = "SELECT * FROM productos";

$resultado = mysqli_query($conexion, $sql);

$productos = [];

while ($fila = mysqli_fetch_assoc($resultado)) {
    $productos[] = $fila;
}

echo json_encode($productos);