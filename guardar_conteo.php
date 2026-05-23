<?php
session_start();
require 'conexion.php';
require 'services/historial_service.php.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);

$id_toma = $input['id_toma'];
$conteos = $input['conteos'];

foreach($conteos as $item){

    $diferencia = $item['stock_fisico'] - $item['stock_sistema'];

    $sql = $conexion->prepare("
        INSERT INTO inventario_detalle
        (
            id_toma,
            id_producto,
            color,
            stock_sistema,
            stock_fisico,
            diferencia,
            id_operario,
            id_sucursal
        )
        VALUES(?,?,?,?,?,?,?,?)
    ");

    $sql->bind_param(
        "iisiiiii",
        $id_toma,
        $item['id_producto'],
        $item['color'],
        $item['stock_sistema'],
        $item['stock_fisico'],
        $diferencia,
        $_SESSION['id'],
        $_SESSION['sucursal']
    );

    $sql->execute();
}

echo json_encode([
    "status"=>"success",
    "mensaje"=>"Conteo guardado correctamente"
]);

registrarHistorial($conexion,$_SESSION['sucursal'],'Conteo Fisico','Inventario','inventario', $id_toma);