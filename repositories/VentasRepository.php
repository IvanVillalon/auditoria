<?php
class VentasRepository {

    public function crearVenta(
    $conexion,
    $cliente,
    $numero_factura,
    $sucursal,
    $total,
    $id_vendedor
) {
     error_log("crearVenta LLAMADO - " . date('H:i:s'));
    $stmt = $conexion->prepare("
        INSERT INTO ventas
        (
            rut_cliente,
            id_vendedor,
            numero_factura,
            id_sucursal,
            total,
            estado,
            fecha
        )
        VALUES (?, ?, ?, ?, ?,'completa', NOW())
    ");

    $stmt->bind_param(
        "sisid",
        $cliente,
        $id_vendedor,
        $numero_factura,
        $sucursal,
        $total
    );
    $stmt->execute();
    return $conexion->insert_id;

    }

    public function crearDetalle($conexion, $venta_id, $producto_id, $cantidad, $precio, $color, $subtotal, $credito_usado) {
        $stmt = $conexion->prepare("
            INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio, color, subtotal, credito_usado)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("iiidsdi", $venta_id, $producto_id, $cantidad, $precio, $color, $subtotal, $credito_usado);
        return $stmt->execute();
    }
public static function getDetalleVentasPorIds($conexion, array $ids) {
    if (empty($ids)) return [];

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $tipos = str_repeat('i', count($ids));

    $stmt = $conexion->prepare("
        SELECT
            p.id,
            p.numero_factura,
            pr.producto,
            p.fecha,
            s.id_producto,
            s.color,
            s.cantidad AS cantidad_vendida,
            IFNULL(dev.devuelto, 0) AS cantidad_devuelta,
            (s.cantidad - IFNULL(dev.devuelto, 0)) AS cantidad_disponible,
            s.precio
        FROM ventas p
        INNER JOIN detalle_venta s ON p.id = s.id_venta
        INNER JOIN producto pr ON s.id_producto = pr.id
        LEFT JOIN (
            SELECT dnc.id_producto, nc.id_venta, SUM(dnc.cantidad) AS devuelto
            FROM detalle_nota_credito dnc
            INNER JOIN nota_credito nc ON dnc.id_nota_credito = nc.id_nota_credito
            GROUP BY dnc.id_producto, nc.id_venta
        ) dev ON dev.id_producto = s.id_producto AND dev.id_venta = p.id
        WHERE p.id IN ($placeholders)
    ");

    $stmt->bind_param($tipos, ...$ids);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
    public function actualizarCreditoCliente($conexion, $rut, $nuevo_credito){
        $stmt = $conexion->prepare("UPDATE cliente SET credito = ? WHERE rut = ?");
        $stmt->bind_param("ds", $nuevo_credito, $rut);
        $stmt->execute();
    }
    public function getCreditoCliente($conexion, $rut){
        $stmt = $conexion->prepare("SELECT credito FROM cliente WHERE rut = ?");
        $stmt->bind_param("s", $rut);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        return $fila['credito'] ?? 0;
    }
    public static function descontarStock($conexion, $producto_id, $sucursal, $color, $cantidad)
    {
        $stmt = $conexion->prepare("
            UPDATE stock_sucursal
            SET cantidad = cantidad - ?
            WHERE id_producto = ? AND id_sucursal = ? AND color = ?
        ");
        $stmt->bind_param("iiis", $cantidad, $producto_id, $sucursal, $color);
        $stmt->execute();
    }
    public function getStockDisponible($conexion, $producto_id, $sucursal, $color){
        $stmt = $conexion->prepare("
            SELECT stock FROM stock_sucursal
            WHERE id_producto = ? AND id_sucursal = ? AND color = ?
        ");
        $stmt->bind_param("iis", $producto_id, $sucursal, $color);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        return $fila['stock'] ?? 0;
    }
}
