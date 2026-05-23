<h1>Bienvenido Admin, <?= $_SESSION['usuario']; ?></h1>

<nav>
    <a href="admin.php?seccion=dashboard">Dashboard</a> |
    <a href="admin.php?seccion=aprobaciones_stock">Aprobaciones</a> |
    <a href="admin.php?seccion=usuarios">Usuarios</a> |
    <a href="logout.php">Cerrar sesión</a>
</nav>