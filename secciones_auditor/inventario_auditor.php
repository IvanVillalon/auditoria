<?php
$sql = "SELECT 
    p.producto,
    p.id_producto,
    d.id_detalle,
    d.color,
    d.stock_sistema,
    d.stock_fisico,
    d.diferencia,
    d.id_sucursal,
    s.nombre,
    d.id_operario,
    d.fecha_registro,
    u.usuario AS operario,

    r.id_reporte,
    r.estado AS estado_reporte,
    r.decision_auditor,
    r.observacion_auditor

FROM inventario_detalle d
JOIN producto p ON p.id_producto = d.id_producto
JOIN usuarios u ON u.id = d.id_operario
JOIN sucursales s ON s.id_sucursal = d.id_sucursal
LEFT JOIN reportes_auditor r 
    ON r.id_referencia = d.id_detalle
    AND r.tipo_reporte = 'conteo'
ORDER BY d.fecha_registro DESC";

$resultado = $conexion->query($sql);
?>

<div class="container mt-4">
    <h3>📊 Auditoría de Inventario</h3>

    <table class="table table-bordered table-hover mt-3">
        <thead class="table-dark">
            <tr>
                <th>Sucursal</th>
                <th>Producto</th>
                <th>Color</th>
                <th>Sistema</th>
                <th>Físico</th>
                <th>Diferencia</th>
                <th>Estado Conteo</th>
                <th>Operario</th>
                <th>Fecha</th>
                <th>Estado Reporte</th>
                <th>Decisión</th>
                <th>Acción</th>
            </tr>
        </thead>

        <tbody>

        <?php while($row = $resultado->fetch_assoc()): 

            $dif = $row['diferencia'];

            if ($dif == 0) {
                $estadoConteo = "<span class='badge bg-success'>OK</span>";
            } elseif ($dif < 0) {
                $estadoConteo = "<span class='badge bg-danger'>FALTA</span>";
            } else {
                $estadoConteo = "<span class='badge bg-warning text-dark'>SOBRA</span>";
            }

            if(!$row['id_reporte']){
                $estadoReporte = "<span class='badge bg-secondary'>Sin reporte</span>";
            } elseif($row['estado_reporte'] == 'pendiente'){
                $estadoReporte = "<span class='badge bg-warning'>Pendiente</span>";
            } elseif($row['estado_reporte'] == 'auditado'){
                $estadoReporte = "<span class='badge bg-info'>Auditado</span>";
            } elseif($row['estado_reporte'] == 'resuelto'){
                $estadoReporte = "<span class='badge bg-success'>Resuelto</span>";
            } elseif($row['estado_reporte'] == 'reconteo'){
                $estadoReporte = "<span class='badge bg-danger'>Reconteo</span>";
            } else {
                $estadoReporte = $row['estado_reporte'];
            }

        ?>

        <tr>
            <td><?= $row['nombre'] ?></td>
            <td><?= $row['producto'] ?></td>
            <td><?= $row['color'] ?: 'Sin color' ?></td>
            <td><?= $row['stock_sistema'] ?></td>
            <td><?= $row['stock_fisico'] ?></td>
            <td><?= $row['diferencia'] ?></td>
            <td><?= $estadoConteo ?></td>
            <td><?= $row['operario'] ?></td>
            <td><?= $row['fecha_registro'] ?></td>
            <td><?= $estadoReporte ?></td>
            <td><?= $row['decision_auditor'] ?: '-' ?></td>

            <td>
                <?php if($row['diferencia'] != 0 && $row['estado_reporte'] == 'pendiente'): ?>
                    <button 
                        class="btn btn-sm btn-primary"
                        onclick="auditarConteo(<?= $row['id_reporte'] ?>)">
                        Auditar
                    </button>
                <?php else: ?>
                    <span class="text-muted">Sin acción</span>
                <?php endif; ?>
            </td>
        </tr>

        <?php endwhile; ?>

        </tbody>
    </table>

    <div id="detalleConteo" style="width:70%; margin:20px auto; display:none;"></div>
</div>