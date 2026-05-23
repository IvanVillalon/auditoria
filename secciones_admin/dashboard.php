<?php
require 'conexion.php';

$sql = $conexion->query("
SELECT 
    r.id_reporte,
    r.tipo_reporte,
    r.titulo,
    r.estado,
    r.decision_auditor,
    r.fecha_reporte,
    u.usuario AS auditor
FROM reportes_auditor r
INNER JOIN usuarios u ON u.id = r.id_auditor
ORDER BY r.fecha_reporte DESC
");
?>

<h3>Dashboard Administrativo</h3>

<table class="table table-bordered table-striped mt-3">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Auditor</th>
            <th>Tipo</th>
            <th>Título</th>
            <th>Estado</th>
            <th>Decisión Auditor</th>
            <th>Fecha</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>

    <?php while($row = $sql->fetch_assoc()): ?>

        <tr>
            <td><?= $row['id_reporte'] ?></td>
            <td><?= $row['auditor'] ?></td>
            <td><?= $row['tipo_reporte'] ?></td>
            <td><?= $row['titulo'] ?></td>
            <td><?= $row['estado'] ?></td>
            <td><?= $row['decision_auditor'] ?: '-' ?></td>
            <td><?= $row['fecha_reporte'] ?></td>

            <td>
                <?php if($row['estado'] == 'auditado'): ?>
                    <button 
                        class="btn btn-success btn-sm"
                        onclick="aprobarReporte(<?= $row['id_reporte'] ?>)">
                        Aprobar
                    </button>
                <?php else: ?>
                    <span class="text-muted"><td>
<?php if ($row['estado'] === 'auditado'): ?>

    <button class="btn btn-success btn-sm"
        onclick="aprobarReporte(<?= $row['id_reporte'] ?>)">
        Aprobar
    </button>

    <button class="btn btn-danger btn-sm"
        onclick="rechazarReporte(<?= $row['id_reporte'] ?>)">
        Rechazar
    </button>

<?php elseif ($row['estado'] === 'pendiente'): ?>

    <span class="badge bg-warning">Esperando auditor</span>

<?php elseif ($row['estado'] === 'resuelto'): ?>

    <span class="badge bg-success">Finalizado</span>

<?php else: ?>

    <span class="text-muted">Sin acción</span>

<?php endif; ?>
</td></span>
                <?php endif; ?>
            </td>
        </tr>

    <?php endwhile; ?>

    </tbody>
</table>