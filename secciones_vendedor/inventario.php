

<?php


$toma = $conexion->query("SELECT *FROM inventario_toma WHERE estado='abierta' LIMIT 1");

if($toma->num_rows == 0){
    echo "<div class='alert alert-warning'>
            No hay toma de inventario activa
          </div>";
    exit;
}

$tomaActiva = $toma->fetch_assoc();
$id_toma = $tomaActiva['id_toma'];

$sql = $conexion->prepare("
SELECT 
    p.id,
    p.producto,
    ss.color,
    ss.stock
FROM inventario ss
INNER JOIN producto p
ON ss.id_producto = p.id
WHERE ss.id_sucursal = ?
ORDER BY p.producto
");
$sql->bind_param("i", $_SESSION['sucursal']);
$sql->execute();
$resultado = $sql->get_result();

?>

<div class="container mt-4">

    <h2 class="text-center mb-4">
        Conteo Físico Inventario
    </h2>

    <table class="table table-bordered table-striped">

        <thead>
            <tr>
                <th>Producto</th>
                <th>Id Producto</th>
                <th>Color</th>
                <th>Stock Sistema</th>
                <th>Conteo Físico</th>
            </tr>
        </thead>

        <tbody>

        <?php while($fila = $resultado->fetch_assoc()){ 
           ?>

            <tr>

                <td><?= $fila['producto'] ?></td>
                <td><?= $fila['id_producto'] ?></td>
                <td><?= $fila['color'] ?></td>
                <td><?= $fila['stock'] ?></td>

                <td>
                    <input
                        type="number"
                        class="form-control conteo"
                        data-producto="<?= $fila['id_producto'] ?>"
                        data-color="<?= $fila['color'] ?>"
                        data-stock="<?= $fila['stock'] ?>"
                        min="0"
                        required>
                </td>

            </tr>

        <?php } ?>

        </tbody>

    </table>

    <div class="text-center">
        <button class="btn btn-primary" onclick="guardarConteo(<?= $id_toma ?>)">
            Guardar Conteo
        </button>
    </div>

</div>