<?php
require_once '../conexion.php';

$rut = $_GET['rut'] ?? null;

if (!$rut) {
    exit("No se recibió el rut");
}

$stmt = $conexion->prepare("
SELECT
    producto.producto,
    ventas.fecha,
    producto.valor_unitario,
    detalle_venta.cantidad,
    detalle_venta.color,
    ventas.total,
    ventas.id_venta,
    ventas.rut_cliente,
    cliente.nombre,
    cliente.apellido
FROM ventas
INNER JOIN detalle_venta
    ON ventas.id_venta = detalle_venta.id_venta
INNER JOIN producto
    ON detalle_venta.id_producto = producto.id_producto
INNER JOIN cliente
    ON ventas.rut_cliente = cliente.rut
WHERE ventas.rut_cliente = ?
");

$stmt->bind_param("s", $rut);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    echo "<table
    border='1'
    id='tablacompras'
    class='table table-striped'
    style='width:50%; text-align:center ; margin:auto;'
    >";
    echo "<h3>Historial de Compras</h3>";

    

    echo "
    <tr>
        <th>ID</th>
        <th>Cliente</th>
        <th>Producto</th>
        <th>Color</th>
        <th>Cantidad</th>
        <th>Valor Unitario</th>
        <th>Total</th>
        <th>Fecha</th>
        <th>Accion</th>
    </tr>";

    while ($fila = $resultado->fetch_assoc()) {

        echo "<tr>";
        echo "<td>{$fila['id_venta']}</td>";
        echo "<td>{$fila['nombre']} {$fila['apellido']}</td>";
        echo "<td>{$fila['producto']}</td>";
        echo "<td>{$fila['color']}</td>";
        echo "<td>{$fila['cantidad']}</td>";
        echo "<td>$" . number_format($fila["valor_unitario"],0,',','.') . "</td>";
        echo "<td>$" . number_format($fila["total"],0,',','.') . "</td>";
        echo "<td>{$fila['fecha']}</td>";
        echo "<td>
            <button
                type='button'
                class='btn btn-sm btn-primary'
                onclick='seleccionarCompra(".$fila["id_venta"].")'>
                Seleccionarcomra
                </button>
                </td>";
        echo "</tr>";
    }

    echo "</table>";

} else {

    echo "<p>No hay compras registradas.</p>";
}
?>


</div>