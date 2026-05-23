<?php

session_start();


require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../controllers/NotaCreditoController.php';


header('Content-Type: application/json');

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

try {

    $response = NotaCreditoController::crear(
        $conexion,
        $data,
        $_SESSION
    );

    echo json_encode($response);

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "mensaje" => $e->getMessage()
    ]);
}
?>