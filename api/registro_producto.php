<?php
session_start();


require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../controllers/ProductoController.php';

header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Método no permitido"
    ]);
    exit;
}

if (!isset($_SESSION['id'])) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Sesión expirada"
    ]);
    exit;
}

try {

    $response = ProductoController::ejecutar(
        $conexion,
        $_POST,
        $_SESSION
    );

    echo json_encode($response);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        "status" => "error",
        "mensaje" => $e->getMessage()
    ]);
}