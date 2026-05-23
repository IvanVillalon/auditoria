<h2>Registro de Productos</h2>

<form action="api/registro_producto.php" method="POST">

    <input type="text" name="nombre" placeholder="Nombre producto" required><br><br>
    
    <input type="number" name="precio" placeholder="Precio" required><br><br>
      <select name="categoria_medicion" required>
        <option value="" disabled selected>Selecciona categoría de medición</option>
        <option value="unidad">Unidad</option>
        <option value="metro">Metro</option>
        <option value="kilo">Kilo</option>
        <option value="litro">Litro</option>
    </select><br><br>
    <select name="categoria_producto" required><br><br>
        <option value="" disabled selected>Selecciona una categoria</option>
        <option value="telas">Telas</option>
        <option value="menaje">Menaje</option>
        <option value="utiles">Utiles</option>
        <option value="ropa_cama">Ropa de cama</option>
    </select><br><br><br>
   
 <button type="submit" name="accion" value="crear">
        Guardar producto
    </button>
</form>