<?php

class AbastecimientoRepository {

    public function getStock($conexion, $id_producto, $id_sucursal, $color) {

        $stmt = $conexion->prepare("
            SELECT stock
            FROM inventario
            WHERE id_producto = ?
            AND id_sucursal = ?
            AND color = ?
        ");

        $stmt->bind_param("iis", $id_producto, $id_sucursal, $color);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc()['stock'] ?? 0;
    }

    public function descontarStock($conexion, $id_producto, $id_sucursal, $cantidad, $color) {

        $stmt = $conexion->prepare("
            UPDATE inventario
            SET stock = stock - ?
            WHERE id_producto = ?
            AND id_sucursal = ?
            AND color = ?
        ");

        $stmt->bind_param("iiis", $cantidad, $id_producto, $id_sucursal, $color);
        $stmt->execute();
    }

    public function sumarStockDestino($conexion, $id_producto, $id_sucursal, $cantidad, $color) {

        $stmt = $conexion->prepare("
            INSERT INTO stock_sucursal (id_producto, id_sucursal, color, stock)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE stock = stock + VALUES(stock)
        ");

        $stmt->bind_param("iisi", $id_producto, $id_sucursal, $color, $cantidad);
        $stmt->execute();
    }

    public function insertAbastecimiento($conexion, $id_producto, $origen, $destino, $cantidad, $color, $comentario, $id_operario) {

        $stmt = $conexion->prepare("
            INSERT INTO abastecimientos
            (id_producto, id_sucursal_origen, id_sucursal_destino, cantidad, color, comentario, id_operario)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iiiissi",
            $id_producto,
            $origen,
            $destino,
            $cantidad,
            $color,
            $comentario,
            $id_operario
        );

        $stmt->execute();

        return $stmt->insert_id;
    }

    public function insertMovimiento($conexion, $tipo, $id_producto, $id_sucursal, $cantidad, $stock_anterior, $stock_nuevo, $id_ref, $comentario, $id_operario, $color) {

        $stmt = $conexion->prepare("
            INSERT INTO movimientos_stock
            (tipo, id_producto, id_sucursal, color, cantidad, stock_anterior, stock_nuevo, id_referencia, comentario, id_operario)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "siisiiiisi",
            $tipo,
            $id_producto,
            $id_sucursal,
            $color,
            $cantidad,
            $stock_anterior,
            $stock_nuevo,
            $id_ref,
            $comentario,
            $id_operario
        );

        $stmt->execute();
    }
}