<?php
require_once __DIR__ . '/../repositories/NotaCreditoRepository.php';
require_once __DIR__ . '/../services/NotaCreditoService.php';
require_once __DIR__ . '/../repositories/VentasRepository.php';
?>

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
/*
//  TRAER SOLO FACTURAS PAGINADAS
$stmt = $conexion->prepare("
SELECT id, numero_factura, rut_cliente,fecha, total, estado AS estado_venta
FROM ventas 
WHERE id_sucursal = ?
ORDER BY id DESC
LIMIT ?, ?
");
$stmt->bind_param("iii",$_SESSION['sucursal'], $inicio, $limite);
$stmt->execute();*/
$resFacturas = NotaCreditoRepository::getVentasPaginadas(
    $conexion,
    $_SESSION['sucursal'],
    $inicio,
    $limite
);

$facturas = $resFacturas;
$idsVentas = [];
foreach ($facturas as $venta) {
    $idsVentas[] = $venta['id'];
}

// 🔹 SI NO HAY FACTURAS
if (empty($idsVentas)) {
    echo "No hay ventas registradas en esta sucursal.";
    return;
}

// 🔹 2. TRAER DETALLE SOLO DE ESAS FACTURAS
$ventas = VentasRepository::getDetalleVentasPorIds($conexion, $idsVentas);

foreach ($ventas as $venta) {
    if (!isset($venta['cantidad_devuelta'])) {
        $venta['cantidad_devuelta'] = 0;
    }
    if (!isset($venta['cantidad_disponible'])) {
        $venta['cantidad_disponible'] = $venta['cantidad_vendida'];
    }
}

// 🔹 3. TOTAL DE PÁGINAS
$total_filas = NotaCreditoRepository::getTotalPaginas($conexion, $_SESSION['sucursal']);
$total_paginas = ceil($total_filas / $limite);

?>

<div id= "tabla_ventas">
<table class='table table-striped table-hover text-center shadow-sm' id='tabla_facturas' style='width:100%; max-width:1400px;'>
<tr>
    <th>Id Venta</th>
    <th>Factura</th>
    <th>Cliente</th>
    <th>Sucursal</th>
    <th>Fecha</th>
    <th>Total</th>
    <th>Estado Venta</th>
    <th>Acción</th>
    <th>Ver Boleta</th>
  </tr>


<?php foreach ($facturas as $venta) {?>

    <tr>
    <td><?php echo $venta["id"]; ?></td>
    <td><?php echo $venta["numero_factura"]; ?></td>
    <td><?php echo $venta["rut_cliente"]; ?></td>
    <td><?php echo $_SESSION['sucursal']; ?></td>
    <td><?php echo $venta["fecha"]; ?></td>
    <td>$ <?php echo number_format($venta["total"], 0, ',', '.'); ?></td>
    <td><?php echo $venta["estado_venta"]; ?></td>
    <td>
         <button type="button" class="btn btn-sm btn-primary" 
                onclick="seleccionarFactura('<?php echo $venta["numero_factura"]; ?>', '<?php echo $venta["id"]; ?>')">
                Seleccionar
            </button>
    </td>
    <td>
        <a class='btn btn-sm btn-primary' href='boleta.php?id_venta=<?php echo $venta["id"]; ?>' target='_blank'>
            <i class='bi bi-receipt'></i> Ver boleta
        </a>
      </td>
    </tr>
    <?php
}
?>
</table><br>
</div><br>
<?php


// 🔹 TABLA DETALLE
?>
<div id="container-detalles" style="display: none;">
    <table  id='tabla_detalles' class='table table-striped table-hover text-center shadow-sm'>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Color</th>
                <th>Vendido</th>
                <th>Devuelto</th>
                <th>Disponible</th>
                <th>Precio</th>
                <th>Seleccionar</th>
                <th>Tipo Nota</th>
                <th>Estado</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody id="cuerpo_tabla_detalle">
            <?php foreach ($ventas as $venta) { 
                $vendido = $venta["cantidad_vendida"];
                $devuelto = $venta["cantidad_devuelta"];
                $disponible = $venta["cantidad_disponible"];
                $disabled = ($disponible <= 0) ? "disabled" : "";
                $color = ($disponible <= 0) ? "style='background:#ffcccc'" : "";
                ?>
                <tr class="detalle"
                    data-factura="<?php echo $venta["numero_factura"]; ?>"
                    data-id="<?php echo $venta["id_producto"]; ?>"
                    data-color="<?php echo $venta["color"]; ?>"
                    style="display:none;" <?php echo $color; ?>>
                    <td><?php echo $venta["producto"]; ?></td>
                    <td><?php echo $venta["color"] ?? 'N/A'; ?></td>
                    <td><?php echo $venta["cantidad_vendida"]; ?></td>
                    <td><?php echo $venta["cantidad_devuelta"]; ?></td>
                    <td><?php echo $venta["cantidad_disponible"]; ?></td>
                    <td>$ <?php echo number_format($venta["precio"], 0, ',', '.'); ?></td>
                    <td>
                        <input type="checkbox" class="check_producto" <?php echo $disabled; ?>>
                    </td>
                    <td>
                        <select  class='tipo_nota_credito form-select' onchange="cambiarTipoNota(this)" required>
            <option value='devolucion'>Devolución de producto</option>
            <option value='ajuste_precio'>Ajuste de precio por producto</option>
        </select>
                        <!--<select class="estado_producto" disabled>
                            <option value="">Seleccione estado</option>
                            <option value="bueno">Bueno</option>
                            <option value="danado">Dañado</option>
                        </select>-->
                    </td>
                    <td>  <select class="estado_producto">
        <option value="">Seleccione estado</option>
        <option value="bueno">Bueno</option>
        <option value="danado">Dañado</option>   
    </select></td>
                    <td>
                        <input type="number" 
                            class="cantidad_devolver" 
                            min="1" 
                            max="<?php echo $disponible; ?>" 
                            value="1"
                            <?php echo $disabled; ?>>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table><br><br>
   
</div>
 <div id='tabla_acciones_nota_credito' class=' gap-2 flex-wrap' style="display: none;">
  
    <div class="ajuste_valor" style="display: none;">
    <label><strong>Ajuste de Valores</strong></label>
    <table class="table table-striped table-hover text-center shadow-sm mt-2">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Color</th>
                <th>Precio Actual</th>
                <th>Nuevo Precio</th>
            </tr>
        </thead>
        <tbody id="cuerpo_ajuste_valores">
            <!-- Se llena dinámicamente desde JS -->
        </tbody>
    </table>
</div>

    <div class='w-100' style='align-items:center;'>
        <label>Comentario para nota de crédito:</label>
        <textarea id='comentario_nota' class='form-control' rows='3'></textarea>
    </div>

    <button class='btn btn-primary' onclick="procesarDevolucion('total')">
        Devolución completa
    </button>

    <button class='btn btn-warning' onclick="procesarDevolucion('parcial')">
        Devolución parcial
    </button>

</div>


<div style='margin-top:15px;'>
<?php
for ($i = 1; $i <= $total_paginas; $i++) {
    if ($i == $pagina) {
        echo "<strong>$i</strong> ";
    } else {
        echo "<a href='?seccion=nota_credito&pagina=$i'>$i</a> ";
    }
}
?>
</div>


<?php
$resultadonotacredito = NotaCreditoRepository::getNotasCredito($conexion, $_SESSION['sucursal']);
$notas_credito = [];
foreach ($resultadonotacredito as $fila) {
    $notas_credito[] = $fila;
}
if (empty($notas_credito)) {?>
    <div style='margin-top:80px; text-align:center;'>
    <h3>Notas de Crédito relacionadas</h3>
    <p>No se han registrado notas de crédito en esta sucursal.</p>
    </div>
    <?php
}else {
    ?>
    <div style='margin-top:80px; text-align:center;'>
        <h3>Notas de Crédito relacionadas</h3>
    </div>
    <div style='display:flex; justify-content:center; width:100%;'>
    <table  class='table table-striped text-center shadow-sm' id='tabla_nota_credito' style='width:90%; max-width:1400px; margin-top:20px;'>
    <tr>
    <th>Id Nota Crédito</th>
    <th>Número Factura</th>
    <th>Fecha</th>
    <th>Estado</th>
    <th>Comentario</th>
    <th>Acción</th>
    </tr>

   <?php foreach ($notas_credito as $nota) {?>
        <tr>
        <td><?php echo $nota["id_nota_credito"];?></td>
        <td><?php echo $nota["numero_factura"];?></td>
        <td><?php echo $nota["fecha"];?></td>
        <td><?php echo $nota["estado"];?></td>
        <td><?php echo $nota["comentario"];?></td>
        <td>
            <a href="comprobante_nota_credito.php?id_nota={$nota['id_nota_credito']}" target='_blank'>
                🧾 Ver boleta
            </a>
          </td>
        </tr>
<?php }?>
</table>
</div><?php
}?>
    
<br><br>
</form>
    <br><br>

           