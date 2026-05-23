<?php

class NotaCreditoRepository {

    public function getVentaDetalle($conexion, $id_venta) {

        $stmt = $conexion->prepare("
            SELECT 
                dv.id_producto,
                dv.color,
                dv.cantidad
            FROM detalle_venta dv
            WHERE dv.id_venta = ?
        ");

        $stmt->bind_param("i", $id_venta);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function crearNota($conexion, $id_venta, $tipo, $comentario, $id_operario) {

        $stmt = $conexion->prepare("
            INSERT INTO nota_credito (id_venta, fecha, estado, comentario, id_operario)
            VALUES (?, NOW(), ?, ?, ?)
        ");

        $stmt->bind_param("issi", $id_venta, $tipo, $comentario, $id_operario);
        $stmt->execute();

        return $stmt->insert_id;
    }

    public function insertarDetalleNota($conexion, $id_nota, $p, $id_operario) {

        $subtotal = $p['precio'] * $p['cantidad'];

        $stmt = $conexion->prepare("
            INSERT INTO detalle_nota_credito
            (id_nota_credito, id_producto, cantidad, precio, subtotal, color, estado_producto, id_operario)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iiiddssi",
            $id_nota,
            $p['id_producto'],
            $p['cantidad'],
            $p['precio'],
            $subtotal,
            $p['color'],
            $p['estado'],
            $id_operario
        );

        $stmt->execute();
    }

    public function sumarStock($conexion, $p, $session) {

        $stmt = $conexion->prepare("
            UPDATE inventario
            SET stock = stock + ?
            WHERE id_producto = ?
            AND id_sucursal = ?
            AND color = ?
        ");

        $stmt->bind_param(
            "iiis",
            $p['cantidad'],
            $p['id_producto'],
            $session['sucursal'],
            $p['color']
        );

        $stmt->execute();
    }

    public function registrarDanado($conexion, $p, $session) {

        $stmt = $conexion->prepare("
            INSERT INTO productos_danados
            (id_producto, id_sucursal, id_venta, cantidad, color, id_operario)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iiiisi",
            $p['id_producto'],
            $session['sucursal'],
            $p['id'],
            $p['cantidad'],
            $p['color'],
            $session['id']
        );

        $stmt->execute();
    }

    public function sumarCreditoCliente($conexion, $rut_cliente, $total) {

        $stmt = $conexion->prepare("
            UPDATE cliente 
            SET credito = credito + ?
            WHERE rut = ?
        ");

        $stmt->bind_param("is", $total, $rut_cliente);
        $stmt->execute();
    }

    public function marcarVentaAnulada($conexion, $id_venta) {

        $stmt = $conexion->prepare("
            UPDATE ventas
            SET estado = 'nota_credito'
            WHERE id = ?
        ");

        $stmt->bind_param("i", $id_venta);
        $stmt->execute();
    }
}