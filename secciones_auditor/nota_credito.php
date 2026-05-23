 <div class="titulo" style="display:flex; flex-direction:column; align-items:center;">

    <h2>Ver Notas de Crédito</h2>

    <input
        type="text"
        id="buscadorNotaCredito"
        placeholder="Buscar por cliente o motivo"
        onkeyup="filtrarTablaNotaCredito()"
        style="width:20%; text-align:center; margin-bottom:10px;"
    >

</div>

<?php

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

if ($pagina < 1) {
    $pagina = 1;
}

$registros_por_pagina = 10;

$inicio = ($pagina - 1) * $registros_por_pagina;

$sql = $conexion->prepare("
SELECT
    nc.id_nota_credito, 
    nc.comentario,
    nc.fecha,

    v.id_venta,
    v.rut_cliente,
    v.id_sucursal,

    s.nombre AS nombre_sucursal,

    v.fecha AS fecha_venta,

    cl.nombre AS nombre_cliente

FROM nota_credito nc

INNER JOIN ventas v
    ON nc.id_venta = v.id_venta

INNER JOIN sucursales s
    ON v.id_sucursal = s.id_sucursal

INNER JOIN cliente cl
    ON v.rut_cliente = cl.rut

GROUP BY nc.id_nota_credito

ORDER BY nc.fecha DESC

LIMIT ?, ?
");

$sql->bind_param("ii", $inicio, $registros_por_pagina);

$sql->execute();

$resultado = $sql->get_result();

if ($resultado->num_rows > 0) {

    echo "
    <table
        border='1'
        id='tablaNotaCredito'
        class='table table-striped'
        style='width:70%; text-align:center; margin:auto;'
    >";

    echo "
    <tr>
        <th>ID</th>
        <th>Rut Cliente</th>
        <th>Nombre Cliente</th>
        <th>Motivo</th>
        <th>Sucursal</th>
        <th>Fecha Emisión</th>
        <th>Fecha Venta</th>
        <th>Acción</th>
    </tr>";

    while ($fila = $resultado->fetch_assoc()) {

        echo "<tr>";

        echo "<td>" . $fila["id_nota_credito"] . "</td>";

        echo "<td>" . $fila["rut_cliente"] . "</td>";

        echo "<td>" . $fila["nombre_cliente"] . "</td>";

        echo "<td>" . $fila["comentario"] . "</td>";

        echo "<td>" . $fila["nombre_sucursal"] . "</td>";

        echo "<td>" . $fila["fecha"] . "</td>";

        echo "<td>" . $fila["fecha_venta"] . "</td>";

        echo "
        <td>

            <button
                type='button'
                class='btn btn-sm btn-primary'
                onclick='seleccionarNota(".$fila["id_nota_credito"].")'>

                Seleccionar

            </button>

        </td>";

        echo "</tr>";
    }

    echo "</table>";

} else {

    echo "<p style='text-align:center;'>No hay notas de crédito registradas.</p>";
}

$total_resultado = $conexion->query("
SELECT COUNT(*) as total
FROM nota_credito
");

$total_filas = $total_resultado->fetch_assoc()['total'];

$total_paginas = ceil($total_filas / $registros_por_pagina);

echo "<div style='text-align:center; margin-top:15px;'>";

for ($i = 1; $i <= $total_paginas; $i++) {

    if ($i == $pagina) {

        echo "<strong style='margin:5px;'>$i</strong>";

    } else {

        echo "
        <a
            style='margin:5px;'
            href='?seccion=historial_registros&pagina=$i'>

            $i

        </a>";
    }
}

echo "</div>";
?>

<!-- DETALLE DINÁMICO -->

<div
    id="detalleNota"
    style="
        width:70%;
        margin:20px auto;
        display:none;
    ">
</div>
