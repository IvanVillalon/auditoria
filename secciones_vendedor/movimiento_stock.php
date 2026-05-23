 <h2>Abastecimiento de sucursales</h2>
<form action="api/abastecimientos_stock_sucursales.php" method="POST">
<?php
include "conexion.php";
$id_bodega_central = 1; // ID de la Bodega Central

// Productos con stock en bodega central
$stmt = $conexion->prepare("
    SELECT DISTINCT p.id, p.producto, p.codigo, s.stock, s.color
    FROM producto p
    INNER JOIN inventario s
        ON p.id = s.id_producto
    WHERE s.id_sucursal = ? AND s.stock > 0
    ORDER BY p.producto
");
$stmt->bind_param("i", $id_bodega_central);
$stmt->execute();
$resultado = $stmt->get_result();
?>

Producto:
<select name="id_producto" id="id_producto" onchange="cargarDatosProducto()" required>
    <option value="" disabled selected>Selecciona un producto</option>
    
    <?php
    while ($fila = $resultado->fetch_assoc()) {
       echo "<option 
    value='" . $fila["id"] . "' 
    data-codigo='" . $fila["codigo"] . "'
>
    " . $fila["producto"] . " (" . $fila["codigo"] . "(". $fila["stock"]. ")</option>";
    }
    ?>
</select>
<input type="hidden" name ="codigo_producto" id="codigo_producto">
<br><br>

<!-- Contenedor de colores -->
<div id="contenedorcolores" style="display:block;">
    Color:
    <select name="color" id="select_color">
        <option value="">Selecciona un color</option>
    </select>
    <br><br>
</div>

Cantidad:
<input type="number" name="cantidad" placeholder="Cantidad" required><br><br>

Sucursal a abastecer:
<?php
$stmt = $conexion->prepare("SELECT * FROM sucursales WHERE id_sucursal != 1");
$stmt->execute();
$resultado = $stmt->get_result();
echo "<select name='id_sucursal' required>";
echo "<option value='' disabled selected>Selecciona una sucursal</option>";
while ($fila = $resultado->fetch_assoc()) {
    echo "<option value='" . $fila["id_sucursal"] . "' data-nombre='".$fila["nombre"] ."'>"
         . $fila["nombre"]. "</option>";
}
echo "</select><br><br>";
?>
<label for ="comentario_abastecimiento">Comentario para el abastecimiento:</label><br>
<textarea id ="comentario_abastecimiento" name="comentario_abastecimiento" rows="3" cols="50" placeholder="Escribe un comentario..." required></textarea><br><br>
<input type="submit" value="Registrar Abastecimiento">
         <?php
         