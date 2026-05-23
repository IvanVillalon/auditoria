<?php

class ProductoRepository {

    public static function getBySucursal($conexion, $sucursal) {
        $stmt = $conexion->prepare("
            SELECT p.id, p.producto, p.valor_unitario, ss.stock, ss.color
            FROM producto p
            INNER JOIN inventario ss ON p.id = ss.id_producto
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
            INNER JOIN inventario ss ON p.id = ss.id_producto
            WHERE p.id = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $idProducto);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
}