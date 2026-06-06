<?php

class NotaCreditoRepository {
    
    public static function getNotasCredito($conexion, $sucursal){
        $stmt = $conexion->prepare("
        SELECT
nc.id_nota_credito,
nc.fecha,
nc.estado,
nc.comentario,
dt.id_detalle_nota_credito,
dt.id_nota_credito,
dt.cantidad,
dt.precio,
dt.subtotal, 
dt.color,
p.id,
p.producto,
p.valor_unitario,
p.codigo,
v.id,
v.numero_factura,
v.rut_cliente,
v.id_sucursal,
v.id_vendedor,
v.estado AS estado_venta,
v.total AS total_venta
FROM nota_credito nc
INNER JOIN detalle_nota_credito dt ON nc.id_nota_credito = dt.id_nota_credito
INNER JOIN producto p ON dt.id_producto = p.id
INNER JOIN ventas v ON nc.id_venta = v.id
WHERE v.id_sucursal = ? ");
$stmt->bind_param("i", $sucursal);
$stmt->execute();
$resultadonotacredito = $stmt->get_result();
        return $resultadonotacredito->fetch_all(MYSQLI_ASSOC);
    }

    public static function getVentasPaginadas(
        $conexion,
        $sucursal,
        $inicio,
        $limite
    ){
        $stmt = $conexion->prepare("
            SELECT
                id,
                numero_factura,
                rut_cliente,
                fecha,
                total,
                estado AS estado_venta
            FROM ventas
            WHERE id_sucursal = ?
            ORDER BY id DESC
            LIMIT ?, ?
        ");
        $stmt->bind_param("iii", $sucursal, $inicio, $limite);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public static function totalVentas($conexion){
        $query = $conexion->prepare("SELECT COUNT(*) total FROM ventas");
        $query->execute();
        return $query->fetch_assoc()['total'];
    }
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