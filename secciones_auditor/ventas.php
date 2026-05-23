

<div class="titulo" style="display:flex; flex-direction:column; align-items:center;">
    <h2>Ver Ventas</h2>
    <input type="text" id="buscarVenta"
    placeholder="Buscar venta por cliente"
    onkeyup="filtrarTablaVenta()"
    style="width: 20%; text-align:center; margin-bottom:10px;">
</div>
<?php
$pagina= isset($_GET['pagina']) ? (int)$_GET['pagina']: 1;
if ($pagina <1){
    $pagina = 1;
}
$registros_por_pagina= 10;
$inicio = ($pagina-1)*$registros_por_pagina;

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
ORDER BY ventas.fecha DESC
LIMIT ?,?
");

$stmt->bind_param("ii", $inicio, $registros_por_pagina);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    echo "<table
    border='1'
    id='tablacompras'
    class='table table-striped'
    style='width:50%; text-align:center ; margin:auto;
    >";
    echo "<h3>Historial de Compras</h3>";

    echo "<table border='1' class='table table-striped'>";

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
$total_resultado = $conexion->query("
SELECT COUNT(*) as total
FROM ventas");
$total_filas = $total_resultado->fetch_assoc()['total'];
$total_paginas = ceil($total_filas / $registros_por_pagina);
echo "<div style='text-align:center; margin-top:15px;'>";
for ($i = 1; $i <= $total_paginas; $i++){
    if ($i == $pagina){
        echo "<strong style='margin:5px;'>$i</strong>";
    }else{
        echo "
        <a
        style='margin:5px;'
        href='?seccion=ventas&pagina=$i'>
        $i
        </a>";
    }
}
echo "</div>";
?>
<div id="detalleCompra"
style="width: 70%; margin:20px auto; display:none;"></div>


</div>