<?php

require "../conexion.php";

$id_venta= isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;
if($id_venta <= 0){
    echo "
    <div class='alert alert-danger'>
        ID de venta invalido
        </div>";
        exit;
}
$sql= $conexion->prepare("
SELECT 
dv.id_detalle,
dv.id_venta,
dv.id_producto,
dv.cantidad,
dv.color,
dv.precio,
dv.credito_usado,
dv.subtotal,
dv.total_venta,
p.id_producto,
p.producto,
v.fecha,
v.numero_factura, 
v.rut_cliente,
v.estado,
c.nombre,
c.apellido,
c.credito,
c.rut

FROM detalle_venta dv
INNER JOIN ventas v
ON dv.id_venta = v.id_venta
INNER JOIN cliente c 
ON v.rut_cliente = c.rut
INNER JOIN producto p 
ON dv.id_producto = p.id_producto
WHERE dv.id_venta = ?
");
$sql->bind_param("i",$id_venta);
$sql->execute();
$resultado = $sql->get_result();
if($resultado->num_rows <= 0){
    echo "
    <div class='alert alert-warning'>
    No existe detalles para esta venta
    </div>";
    exit;
}
$primeraFila= $resultado->fetch_assoc();
?>
<div class="card shadow p-3">
    <h3 class="mb-3">
        Detalle Venta #<?= $id_venta ?>
    </h3>
    <div class="mb-3">
        <strong>Fecha:</strong>
        <?= htmlspecialchars($primeraFila['fecha']) ?>
    </div>
    <div class="mb-3">
        <strong>Cliente</strong>
        <?= htmlspecialchars($primeraFila['nombre']) ?>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Color</th>
                <th>Precio Unitario</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
              <?php

            do {

                echo "<tr>";

                echo "<td>" . htmlspecialchars($primeraFila['producto']) . "</td>";

                echo "<td>" . htmlspecialchars($primeraFila['color']) . "</td>";

                echo "<td>$" . $primeraFila['precio'] . "</td>";

                echo "<td>" .$primeraFila['cantidad'] . "</td>";

                echo "<td>$" . number_format($primeraFila['subtotal']) . "</td>";

                echo "<td>" . htmlspecialchars($primeraFila['estado']) . "</td>";

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
            id="accion<?= $id_venta ?>"
            class="form-select"
            onchange="mostrarAlerta(<?= $id_venta ?>)">
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
            id="prioridad<?= $id_venta ?>"
            class="form-select">
            <option value="baja">Baja</option>
            <option value="media">Media</option>

                <option value="alta">Alta</option>

                <option value="critica">Crítica</option>

        </select>
        </div>
    </div>
<div id="divAlerta<?= $id_venta ?>"
style="display:none;"
class="mt-3">
<label class="form-label">
    Observaciones Auditor
</label>
<textarea
id="textoAlerta<?=  $id_venta ?>"
class="form-control"
rows="4"
placeholder="Ingrese el motivo de la laerta"></textarea>
</div>
<div class="mt-3">
    <button
        class="btn btn-danger"
        onclick="procesarventa(<?= $id_venta ?>)">
        Procesar Venta
    </button>
</div>
</div>
