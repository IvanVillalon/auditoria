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

    $stmt = $conexion->prepare("
        INSERT INTO ventas
        (
            rut_cliente,
            id_vendedor,
            numero_factura,
            id_sucursal,
            total,
            fecha
        )
        VALUES (?, ?, ?, ?, ?, NOW())
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

    public function crearDetalle($conexion, $venta_id, $producto_id, $cantidad, $precio, $color) {
        $stmt = $conexion->prepare("
            INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio, color)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("iiids", $venta_id, $producto_id, $cantidad, $precio, $color);
        return $stmt->execute();
    }
    public static function getdetalleVentas($conexion,$id_venta){
        $query = "
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
    SELECT 
        dnc.id_producto,
        nc.id_venta,
        SUM(dnc.cantidad) AS devuelto
    FROM detalle_nota_credito dnc
    INNER JOIN nota_credito nc 
        ON dnc.id_nota_credito = nc.id_nota_credito
    GROUP BY dnc.id_producto, nc.id_venta
) dev 
ON dev.id_producto = s.id_producto 
AND dev.id_venta = p.id
WHERE p.id IN ($id_venta)";
        $resultado = $conexion->query($query);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
}

?>