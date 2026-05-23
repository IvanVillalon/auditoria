
  <h1>Bienvenido Auditor, <?php echo $_SESSION['usuario']; ?>!</h1>
<nav>
    <a href="auditor.php?seccion=permisos">Permisos de operarios</a> |
    <a href="auditor.php?seccion=historial_registros">Historial de registros</a> |
    <a href="auditor.php?seccion=nota_credito">Nota de Crédito</a> |
    <a href="auditor.php?seccion=devoluciones">Registrar Devolución</a> |
    <a href="auditor.php?seccion=ventas">Ventas</a> |
    <a href="auditor.php?seccion=inventario_auditor">Inventario</a> |
    <a href="auditor.php?seccion=registro_clientes">Clientes</a> |
    <a href="logout.php">Cerrar sesión</a>
</nav>