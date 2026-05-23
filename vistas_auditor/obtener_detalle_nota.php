<?php

require "../conexion.php";


$id_nota = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;

if ($id_nota <= 0) {

    echo "
    <div class='alert alert-danger'>
        ID de nota inválido
    </div>";

    exit;
}

$sql = $conexion->prepare("
SELECT

    dn.id_detalle,
    dn.id_nota_credito,
    dn.id_producto,
    dn.cantidad,
    dn.precio,
    dn.subtotal,
    dn.color,
    dn.estado_producto,

    nc.comentario,
    nc.fecha,

    p.producto,

    u.usuario AS operario

FROM detalle_nota_credito dn

INNER JOIN nota_credito nc
    ON dn.id_nota_credito = nc.id_nota_credito

INNER JOIN producto p
    ON dn.id_producto = p.id_producto

LEFT JOIN usuarios u
    ON dn.id_operario = u.id

WHERE dn.id_nota_credito = ?
");

$sql->bind_param("i", $id_nota);

$sql->execute();

$resultado = $sql->get_result();

if ($resultado->num_rows <= 0) {

    echo "
    <div class='alert alert-warning'>
        No existe detalle para esta nota
    </div>";

    exit;
}

$primeraFila = $resultado->fetch_assoc();

?>

<div class="card shadow p-3">

    <h3 class="mb-3">
        Detalle Nota Crédito #<?= $id_nota ?>
    </h3>

    <div class="mb-3">

        <strong>Comentario:</strong>

        <?= htmlspecialchars($primeraFila['comentario']) ?>

    </div>

    <div class="mb-3">

        <strong>Fecha:</strong>

        <?= $primeraFila['fecha'] ?>

    </div>

    <div class="mb-3">

        <strong>Operario:</strong>

        <?= htmlspecialchars($primeraFila['operario']) ?>

    </div>

    <table class="table table-bordered table-striped">

        <thead>

            <tr>

                <th>Producto</th>
                <th>Color</th>
                <th>Cantidad</th>
                <th>Precio</th>
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

                echo "<td>" . $primeraFila['cantidad'] . "</td>";

                echo "<td>$" . number_format($primeraFila['precio']) . "</td>";

                echo "<td>$" . number_format($primeraFila['subtotal']) . "</td>";

                echo "<td>" . htmlspecialchars($primeraFila['estado_producto']) . "</td>";

                echo "</tr>";

            } while ($primeraFila = $resultado->fetch_assoc());

            ?>

        </tbody>

    </table>

    <hr>

    <div class="row g-3">

        <div class="col-md-4">

            <label class="form-label">

                Acción Auditor

            </label>

            <select
                id="accion<?= $id_nota ?>"
                class="form-select"
                onchange="mostrarAlerta(<?= $id_nota ?>)">

                <option value="">
                    Seleccionar acción
                </option>


                <option value="solicitardetalle">
                    Solicitar mayores detalles
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
                id="prioridad<?= $id_nota ?>"
                class="form-select">

                <option value="baja">Baja</option>

                <option value="media">Media</option>

                <option value="alta">Alta</option>

                <option value="critica">Crítica</option>

            </select>

        </div>

    </div>

    <div
        id="divAlerta<?= $id_nota ?>"
        style="display:none;"
        class="mt-3">

        <label class="form-label">

            Observación Auditor

        </label>

        <textarea
            id="textoAlerta<?= $id_nota ?>"
            class="form-control"
            rows="4"
            placeholder="Ingrese el motivo de la alerta"></textarea>

    </div>

    <div class="mt-3">

        <button
            class="btn btn-danger"
            onclick="procesarnotacredito(<?= $id_nota ?>)">

            Procesar Nota

        </button>

    </div>

</div>

<script>

function mostrarAlerta(id){

    let accion = document.getElementById(
        "accion" + id
    ).value;

    let div = document.getElementById(
        "divAlerta" + id
    );

    if (
        accion === "alertar" ||
        accion === "solicitardetalle"
    ) {

        div.style.display = "block";

    } else {

        div.style.display = "none";
    }
}

</script>