<h1>Bienvenido Admin, <?= $_SESSION['usuario'].", Sucursal: ". $_SESSION['sucursal_nombre'];?></h1>

<nav>
    <a href="vendedor.php?secciones_vendedor=inventario">Inventario</a> |
    <a href="vendedor.php?secciones_vendedor=ventas">Ventas</a>|
    <a href="vendedor.php?secciones_vendedor=movimiento_stock">Movimiento Stock</a> |
    <a href="vendedor.php?secciones_vendedor=politicas_devolucion">Politicas de devolucion</a> |
    <a href="vendedor.php?secciones_vendedor=nota_credito">Nota de credito</a>|
    <a href="vendedor.php?secciones_vendedor=registro_clientes">Registro de clientes</a>|
    <a href="vendedor.php?secciones_vendedor=registro_productos">Registro Productos</a>|
    <a href="logout.php">Cerrar sesión</a>
</nav>