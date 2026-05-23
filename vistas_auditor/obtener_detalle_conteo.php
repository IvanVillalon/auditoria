<?php
require "../conexion.php";
$id_conteo = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0 ;
if($id_conteo<=0){
    echo"
    <div class= 'alert alert-danger'>
    ID de conteo invalido
    </div>";
    exit;
}
$sql= $conexion->prepare("SELECT 
id.id_detalle,
id.id_toma,
id.id_producto,
id.color,
id.stock_sistema,
id.stock_fisico,
id.diferencia,
id.id_operario,
id.id_sucursal,
id.fecha_registro,
s.id_sucursal,
s.nombre,
p.id_producto,
p.producto,
o.id,
o.usuario

 FROM inventario_detalle id 
 INNER JOIN producto p ON p.id_producto = id.id_producto
 INNER JOIN sucursales s ON s.id_sucursal = id.id_sucursal
 INNER JOIN usuarios o ON o.id= id.id_operario
 WHERE id.id_detalle = ?");

$sql->bind_param("i",$id_conteo);
$sql->execute();
$resultado = $sql->get_result();
if($resultado->num_rows <= 0){
    echo "
    <div class='alert alert-warning'>
    No existen detalles para ese conteo
    </div>";
    exit;
    }

$primeraFila= $resultado->fetch_assoc();
?>
<div class="card shadow p-3">
    <h3 class="mb-3">
        Detalle Conteo#<?= $id_conteo ?>
    </h3>
    <div class="mb-3">
        <strong>Fecha:</strong>
        <?= htmlspecialchars($primeraFila['fecha_registro']) ?>
    </div>
    <div class="mb-3">
        <strong>Sucursal:</strong>
        <?= htmlspecialchars($primeraFila['nombre']) ?>
    </div>
    <div class="mb-3">
        <strong>Operario:</strong>
        <?= htmlspecialchars($primeraFila['usuario']) ?>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Producto Contado</th>
                <th>Color</th>
                <th>Cantidad Fisica</th>
                <th>Cantidad Sistema</th>
            </tr>
        </thead>
        <tbody>
              <?php

            do {

                echo "<tr>";

                echo "<td>" . htmlspecialchars($primeraFila['producto']) . "</td>";

                echo "<td>" . htmlspecialchars($primeraFila['color']) . "</td>";

                echo "<td>" . $primeraFila['stock_fisico'] . "</td>";

                echo "<td>" .$primeraFila['stock_sistema'] . "</td>";

    
                echo "</tr>";

            } while ($primeraFila = $resultado->fetch_assoc());

            ?>

        </tbody>
    </table>
    <hr>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">
                Accion Auditor
            </label>
            <select
            id="accion<?= $id_conteo ?>"
            class="form-select"
            onchange="mostrarAlerta(<?= $id_conteo ?>)">
            <option value="">
                Seleccione una accion
            </option>
            <option value="solicitardetalle">
                Solicitar mas detalles
            </option>
            <option value="alertar">
                Alertar
            </option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">
                Prioridad
            </label>
            <select
            id="prioridad<?= $id_conteo ?>"
            class="form-select">
            <option value="baja">Baja</option>
            <option value="media">Media</option>

                <option value="alta">Alta</option>

                <option value="critica">Crítica</option>

        </select>
        </div>
    </div>
<div id="divAlerta<?= $id_conteo ?>"
style="display:none;"
class="mt-3">
<label class="form-label">
    Observaciones Auditor
</label>
<textarea
id="textoAlerta<?=  $id_conteo ?>"
class="form-control"
rows="4"
placeholder="Ingrese el motivo de la laerta"></textarea>
</div>
<div class="mt-3">
    <button
        class="btn btn-danger"
        onclick="procesarreporteconteo(<?= $id_conteo ?>)">
        Procesar Reporte
    </button>
</div>
</div>
