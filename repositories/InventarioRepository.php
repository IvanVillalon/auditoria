<?php

class InventarioRepository {

    // 🔵 1. Obtener inventario completo por sucursal
    public static function getBySucursal($conexion, $sucursal) {
        $stmt = $conexion->prepare("
            SELECT 
                ss.id_producto,
                p.producto,
                ss.stock,
                ss.color,
                ss.id_sucursal
            FROM inventario ss
            INNER JOIN producto p ON p.id = ss.id_producto
            WHERE ss.id_sucursal = ?
        ");

        $stmt->bind_param("i", $sucursal);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 🔵 2. Obtener stock de un producto específico
    public static function getStockProducto($conexion, $idProducto, $sucursal) {
        $stmt = $conexion->prepare("
            SELECT stock, color
            FROM inventario
            WHERE id_producto = ? AND id_sucursal = ?
            LIMIT 1
        ");

        $stmt->bind_param("ii", $idProducto, $sucursal);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // 🔵 3. Validar si hay stock suficiente
    public static function validarStock($conexion, $idProducto, $sucursal, $cantidad) {
        $data = self::getStockProducto($conexion, $idProducto, $sucursal);

        if (!$data) return false;

        return $data['stock'] >= $cantidad;
    }

    // 🔵 4. Reducir stock (venta)
    public static function reducirStock($conexion, $idProducto, $sucursal, $cantidad) {
        $stmt = $conexion->prepare("
            UPDATE inventario 
            SET stock = stock - ?
            WHERE id_producto = ? AND id_sucursal = ?
        ");

        $stmt->bind_param("iii", $cantidad, $idProducto, $sucursal);

        return $stmt->execute();
    }

    // 🔵 5. Aumentar stock (devolución / ingreso)
    public static function aumentarStock($conexion, $idProducto, $sucursal, $cantidad) {
        $stmt = $conexion->prepare("
            UPDATE inventario 
            SET stock = stock + ?
            WHERE id_producto = ? AND id_sucursal = ?
        ");

        $stmt->bind_param("iii", $cantidad, $idProducto, $sucursal);

        return $stmt->execute();
    }
}