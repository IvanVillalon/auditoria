<?php

$sql = $conexion->query("
SELECT 
    r.id_reporte,
    p.producto,
    i.color,
    i.stock_sistema,
    i.stock_fisico,
    r.decision_auditor,
    r.observacion_auditor
FROM reportes_auditor r
INNER JOIN inventario_detalle i ON i.id_detalle = r.id_referencia
INNER JOIN producto p ON p.id_producto = i.id_producto
WHERE r.estado = 'auditado'
");
?>

<h3>Aprobaciones pendientes</h3>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Color</th>
            <th>Sistema</th>
            <th>Físico</th>
            <th>Decisión Auditor</th>
            <th>Observación</th>
            <th>Acción</th>
        </tr>
    </thead>

    <tbody>
        <?php while($row = $sql->fetch_assoc()): ?>
        <tr>
            <td><?= $row['producto'] ?></td>
            <td><?= $row['color'] ?: 'Sin color' ?></td>
            <td><?= $row['stock_sistema'] ?></td>
            <td><?= $row['stock_fisico'] ?></td>
            <td><?= $row['decision_auditor'] ?></td>
            <td><?= $row['observacion_auditor'] ?></td>
            <td>
                <button 
                    class="btn btn-success"
                    onclick="aprobarStock(<?= $row['id_reporte'] ?>)">
                    Aprobar
                </button>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>