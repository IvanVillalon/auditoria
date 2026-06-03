<?php

class ProductoRepository {

    public static function getBySucursal($conexion, $sucursal) {
        $stmt = $conexion->prepare("
            SELECT p.id, p.producto, p.valor_unitario, ss.stock, ss.color
            FROM producto p
            INNER JOIN stock_sucursal ss ON p.id = ss.id_producto
            WHERE ss.id_sucursal = ?
        ");

        $stmt->bind_param("i", $sucursal);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function getProductoVenta($conexion, $idProducto)
    {
        $stmt = $conexion->prepare("
            SELECT p.valor_unitario, ss.color
            FROM producto p
            INNER JOIN stock_sucursal ss ON p.id = ss.id_producto
            WHERE p.id = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $idProducto);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
    public static function obtenerProductoPorId($conexion, $id) {

    $stmt = $conexion->prepare("
        SELECT
            id,
            producto,
            valor_unitario,
            categoria_producto,
            categoria_medicion
        FROM producto
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $resultado = $stmt->get_result();

    return $resultado->fetch_assoc();
}
public static function descontarStock($conexion, $producto_id, $cantidad, $sucursal)
{
    $stmt = $conexion->prepare("
        UPDATE stock_sucursal
        SET stock = stock - ?
        WHERE id_producto = ?
        AND id_sucursal = ?
        ");

    $stmt->bind_param("iii", $cantidad, $producto_id, $sucursal);
    $stmt->execute();
}
}