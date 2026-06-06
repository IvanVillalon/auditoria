<h2>Actualizar Producto</h2>
<?php
/** @var array $productos */
?>
<select name="id_producto" id="producto_actualizar">

    <option value="">Seleccione un producto</option>

    <?php foreach($productos as $producto): ?>

        <option value="<?= $producto['id'] ?>">
            <?= $producto['producto'] ?>
        </option>

    <?php endforeach; ?>

</select>

<hr>

<h3>Datos actuales</h3>

<p>
    <strong>Precio actual:</strong>
    <span id="precio_actual">-</span>
</p>

<p>
    <strong>Categoría actual:</strong>
    <span id="categoria_actual">-</span>
</p>

<p>
    <strong>Medición actual:</strong>
    <span id="medicion_actual">-</span>
</p>

<hr>

<h3>Nuevos datos</h3>

<form action="api/actualizar_producto.php" method="POST">

    <input type="hidden" name="id_producto" id="id_producto_hidden">

    <input
        type="number"
        name="nuevo_precio"
        placeholder="Nuevo precio"
    >

    <br><br>

    <select name="nueva_categoria_producto">

        <option value="">Nueva categoría</option>
        <option value="telas">Telas</option>
        <option value="menaje">Menaje</option>
        <option value="utiles">Útiles</option>
        <option value="ropa_cama">Ropa de cama</option>

    </select>

    <br><br>

    <select name="nueva_categoria_medicion">

        <option value="">Nueva medición</option>
        <option value="unidad">Unidad</option>
        <option value="metro">Metro</option>
        <option value="kilo">Kilo</option>
        <option value="litro">Litro</option>

    </select>

    <br><br>

    <button type="submit">
        Actualizar producto
    </button>

</form>