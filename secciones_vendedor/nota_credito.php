<form onsubmit="return false;">
    <div class="titulo" style="display:flex; justify-content:center; align-items:center; flex-direction:column;">
        <h2>Registrar Nota de Crédito</h2>
<input 
type="text" 
id="buscador" 
placeholder="Buscar factura"
onkeyup="filtrarTabla()"
class="form-control"
style="max-width:400px; margin-top:15px;">
    </div>
<br><br>

<?php 

// PAGINACIÓN
$limite = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina - 1) * $limite;

//  TRAER SOLO FACTURAS PAGINADAS
$stmt = $conexion->prepare("
SELECT id, numero_factura, rut_cliente,fecha, total, estado AS estado_venta
FROM ventas 
WHERE id_sucursal = ?
ORDER BY id DESC
LIMIT ?, ?
");
$stmt->bind_param("iii",$_SESSION['sucursal'], $inicio, $limite);
$stmt->execute();
$resFacturas = $stmt->get_result();

$facturas = [];
$idsVentas = [];

while ($fila = $resFacturas->fetch_assoc()) {
    $facturas[] = $fila;
    $idsVentas[] = $fila['id'];
}

// 🔹 SI NO HAY FACTURAS
if (empty($idsVentas)) {
    echo "No hay ventas registradas en esta sucursal.";
    return;
}

// 🔹 2. TRAER DETALLE SOLO DE ESAS FACTURAS
$ids = implode(",", $idsVentas);

$queryDetalle = "
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

WHERE p.id IN ($ids)
";

$resultado = $conexion->query($queryDetalle);

$ventas = [];
while ($fila = $resultado->fetch_assoc()) {
    $ventas[] = $fila;
}

// 🔹 3. TOTAL DE PÁGINAS
$total_query = $conexion->query("SELECT COUNT(*) as total FROM ventas");
$total_filas = $total_query->fetch_assoc()['total'];
$total_paginas = ceil($total_filas / $limite);


// 🔹 TABLA FACTURAS
echo "<div style='display:flex; justify-content:center; width:100%; margin-top:20px;'>";
echo "<table class='table table-striped table-hover text-center shadow-sm' id='tabla_facturas' style='width:90%; max-width:1400px;'>";
echo "<tr>
        <th>Id Venta</th>
        <th>Factura</th>
        <th>Cliente</th>
        <th>Sucursal</th>
        <th>Fecha</th>
        <th>Total</th>
        <th>Estado Venta</th>
        <th>Acción</th>
        <th>Ver Boleta</th>
      </tr>";

foreach ($facturas as $venta) {

    echo "<tr>";
    echo "<td>{$venta["id"]}</td>";
    echo "<td>{$venta["numero_factura"]}</td>";
    echo "<td>{$venta["rut_cliente"]}</td>";
    echo "<td>{$_SESSION['sucursal']}</td>";
    echo "<td>{$venta["fecha"]}</td>";
    echo "<td>$ " . number_format($venta["total"], 0, ',', '.') . "</td>";
    echo "<td>{$venta["estado_venta"]}</td>";
    echo '<td>
    <button type="button" class="btn btn-sm btn-primary" onclick="seleccionarFactura(\'' . $venta["numero_factura"] . '\', ' . $venta["id"] . ')">
    Seleccionar
    </button>
    </td>';
   echo "<td>
        <a class='btn btn-sm btn-primary' href='boleta.php?id_venta={$venta["id"]}' target='_blank'>
            <i class='bi bi-receipt'></i> Ver boleta
        </a>
      </td>";
    echo "</tr>";
}

echo "</table><br>";
echo "</div><br>";


// 🔹 TABLA DETALLE
echo "<div style='display:flex; justify-content:center; width:100%; margin-top:30px;'>";
echo "<table class='table table-striped text-center shadow-sm' id='tabla_detalle' style='display:none; width:90%; max-width:1400px;'>";
echo "<thead>
<tr>
    <th>Producto</th>
    <th>Color</th>
    <th>Vendido</th>
    <th>Devuelto</th>
    <th>Disponible</th>
    <th>Precio</th>
    <th>Seleccionar</th>
    <th>Estado</th>
    <th>Cantidad</th>
</tr>
</thead><tbody>";

foreach ($ventas as $venta) {

    $vendido = $venta["cantidad_vendida"];
    $devuelto = $venta["cantidad_devuelta"];
    $disponible = $venta["cantidad_disponible"];

    $disabled = ($disponible <= 0) ? "disabled" : "";
    $color = ($disponible <= 0) ? "style='background:#ffcccc'" : "";
    
  echo "<tr class='detalle '
    data-factura='".$venta["numero_factura"]."' 
    data-id='".$venta["id_producto"]."' 
    data-color='".$venta["color"]."'
    style='display:none;' $color>";

echo "<td>".$venta["producto"]."</td>";
echo "<td>".$venta["color"]."</td>";
echo "<td>$vendido</td>";
echo "<td>$devuelto</td>";
echo "<td>$disponible</td>";
echo "<td>$ " . number_format($venta["precio"], 0, ',', '.') . "</td>";

echo "<td>
    <input type='checkbox' class='check_producto' $disabled>
</td>";

echo "<td>
    <select class='estado_producto' disabled>
        <option value=''>Seleccione estado</option>
        <option value='bueno'>Bueno</option>
        <option value='danado'>Dañado</option>
    </select>
</td>";

echo "<td>
    <input type='number' 
        class='cantidad_devolver' 
        min='1' 
        max='$disponible' 
        value='1'
        $disabled>
</td>";

echo "</tr>";
}
echo "</tbody></table><br><br>";
echo "</div>";

// 🔹 BOTONES
echo "<div id='tabla_acciones_nota_credito' style='display:none; width:50%; justify-content:center; align-items:center;' class='d-flex gap-3 flex-wrap'>";

echo "
<div id='tabla_acciones_nota_credito' style='display:none; margin-top:15px; width:50%; justify-content:center; aling-items:center;' class='d-flex gap-2 flex-wrap'>

    <div class='w-100' sytyle:aling-items:center;>
        <label>Comentario para nota de crédito:</label>
        <textarea id='comentario_nota' class='form-control' rows='3'></textarea>
    </div>

    <button class='btn btn-primary' onclick=\"procesarDevolucion('total')\">
        Devolución completa
    </button>

    <button class='btn btn-warning' onclick=\"procesarDevolucion('parcial')\">
        Devolución parcial
    </button>

</div>
";
echo "</div>";

// 🔹 PAGINACIÓN VISUAL
echo "<div style='margin-top:15px;'>";

for ($i = 1; $i <= $total_paginas; $i++) {
    if ($i == $pagina) {
        echo "<strong>$i</strong> ";
    } else {
        echo "<a href='?seccion=nota_credito&pagina=$i'>$i</a> ";
    }
}

echo "</div>";

$querynotacredito = "
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
WHERE v.id_sucursal = ? ";

$resultadonotacredito = $conexion->prepare($querynotacredito);
$resultadonotacredito->bind_param("i", $_SESSION['sucursal']);
$resultadonotacredito->execute();
$resultadonotacredito = $resultadonotacredito->get_result();
$notas_credito = [];
while ($fila = $resultadonotacredito->fetch_assoc()) {
    $notas_credito[] = $fila;
}
if (empty($notas_credito)) {
    echo "No hay notas de crédito relacionadas con esta sucursal.";
}else {
    echo "<div style='margin-top:80px; text-align:center;'>";
echo "<h3>Notas de Crédito relacionadas</h3>";
echo "</div>";
echo "<div style='display:flex; justify-content:center; width:100%;'>";
echo "<table border='1' class='table table-striped text-center shadow-sm' id='tabla_nota_credito' style='width:90%; max-width:1400px; margin-top:20px;'>";
echo "<tr>
    <th>Id Nota Crédito</th>
    <th>Número Factura</th>
    <th>Fecha</th>
    <th>Estado</th>
    <th>Comentario</th>
    <th>Acción</th>
    </tr>";

    foreach ($notas_credito as $nota) {
        echo "<tr>";
        echo "<td>{$nota["id_nota_credito"]}</td>";
        echo "<td>{$nota["numero_factura"]}</td>";
        echo "<td>{$nota["fecha"]}</td>";
        echo "<td>{$nota["estado"]}</td>";
        echo "<td>{$nota["comentario"]}</td>";
           echo "<td>
            <a href='comprobante_nota_credito.php?id_nota={$nota["id_nota_credito"]}' target='_blank'>
                🧾 Ver boleta
            </a>
          </td>";
        echo "</tr>";
    }




echo "</table>";
echo "</div>";
}
?>
    
<br><br>
</form>
    <br><br>
           