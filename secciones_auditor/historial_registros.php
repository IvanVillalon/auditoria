<div class="container mt-4">

    <h1 class="text-center mb-4">Historial Registros</h1>


    <?php
    $registros_por_pagina = 10;
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if($pagina < 1) $pagina = 1;

    $inicio = ($pagina - 1) * $registros_por_pagina;

    $sql = $conexion->prepare("
        SELECT * FROM historial_acciones
        ORDER BY id_historial DESC
        LIMIT ?, ?
    ");

    $sql->bind_param("ii", $inicio, $registros_por_pagina);
    $sql->execute();
    $rs = $sql->get_result();
    ?>

    <div id="acciones_operarios">

        <div class="card shadow p-3">

            <table class="table table-bordered table-striped text-center">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Operario</th>
                        <th>Sucursal</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>Tabla</th>
                        <th>Registro</th>
                        <th>IP</th>
                        <th>Fecha</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while($fila = $rs->fetch_assoc()){ ?>

                    <tr>
                        <td><?= $fila["id_historial"] ?></td>
                        <td><?= $fila["id_usuario"] ?></td>
                        <td><?= $fila["id_sucursal"] ?></td>
                        <td><?= $fila["accion"] ?></td>
                        <td><?= $fila["descripcion"] ?></td>
                        <td><?= $fila["tabla_afectada"] ?></td>
                        <td><?= $fila["id_registro"] ?></td>
                        <td><?= $fila["ip_usuario"] ?></td>
                        <td><?= $fila["fecha"] ?></td>
                        
                    </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

        <?php
        $total = $conexion->query("SELECT COUNT(*) total FROM historial_acciones");
        $total_filas = $total->fetch_assoc()['total'];
        $total_paginas = ceil($total_filas / $registros_por_pagina);
        ?>

        <div class="text-center mt-3">

            <?php for($i=1; $i<=$total_paginas; $i++){ ?>

                <a
                    class="btn btn-outline-dark btn-sm mx-1"
                    href="auditor.php?seccion=historial_registros&pagina=<?= $i ?>">
                    <?= $i ?>
                </a>

            <?php } ?>

        </div>

    </div>

    <div id="detalleacciones"
         class="card shadow p-3 mt-4"
         style="display:none;">
    </div>

</div>