
        <h2>Listado de Clientes</h2>
<?php
?>
<input type="text" id="buscador" placeholder="Buscar por nombre, apellido o rut" onkeyup="filtrarTabla()">
        <br><br>
<?php
$registros_por_pagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1){
    $pagina = 1;
}
$inicio = ($pagina - 1) * $registros_por_pagina;
    $stmt = $conexion->prepare("SELECT id, nombre,rut,  correo, apellido, fecha_registro FROM cliente LIMIT ?, ?");
    $stmt->bind_param("ii", $inicio, $registros_por_pagina);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows > 0) {
        echo "<table border='1' id='tablaClientes'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Rut</th><th>Apellido</th><th>Correo</th><th>Fecha de Registro</th><th>Accion</th></tr>";
        while ($fila = $resultado->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $fila["id"] . "</td>";
            echo "<td>" . $fila["nombre"] . "</td>";
            echo "<td>" . $fila["rut"] . "</td>";
            echo "<td>" . $fila["apellido"] . "</td>";
            echo "<td>" . $fila["correo"] . "</td>";
            echo "<td>" . $fila["fecha_registro"] . "</td>";
       echo "<td>
    <button 
        onclick=\"verCompras('{$fila['rut']}')\" 
        class='btn btn-success'>
        Ver compras
    </button>
</td>";
            echo "</tr>";
        }
        echo "</table>";
        ?>
        <?php
        $total_resultado = $conexion->query("SELECT COUNT(*) as total FROM cliente");
$total_filas = $total_resultado->fetch_assoc()['total'];

$total_paginas = ceil($total_filas / $registros_por_pagina);

echo "<br>";

for ($i = 1; $i <= $total_paginas; $i++) {
     if ($i == $pagina) {
        echo "<strong style='margin:5px;'>$i</strong>";
    } else {
        echo "<a style='margin:5px;' href='?pagina=$i'>$i</a>";
    }
}?>
        <div id="compras"></div>
        
        <?php
    } else {
        echo "No hay clientes registrados.";


    }
    ?>
    <div id="detalleCompra" style="width: 70%; margin:20px auto; display:none;">