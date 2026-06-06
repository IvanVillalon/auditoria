<?php

session_start();

require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/services/VentaService.php';

verificarLogin('vendedor');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
    exit();
}

try {

    $id_venta = VentaService::finalizarVenta(
        $conexion,
        $_SESSION['carrito']  ?? [],
        $_SESSION['cliente']  ?? '',
        $_SESSION['sucursal'] ?? 0,
        $_SESSION['id']       ?? 0
    );

    unset($_SESSION['carrito']);

    echo json_encode([
        'success'  => true,
        'id_venta' => $id_venta
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'mensaje' => $e->getMessage()
    ]);
}
?>
