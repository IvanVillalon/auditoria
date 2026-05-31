<?php

require_once 'core/db.php';
require_once 'core/auth.php';

require_once 'services/VentaService.php';
require_once 'repositories/ClienteRepository.php';
require_once 'repositories/ProductoRepository.php';
require_once 'repositories/VentasRepository.php';



if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

/* =========================
   ACCIONES (SOLO ORQUESTA)
========================= */

if (isset($_POST['agregar'])) {

    $_SESSION['cliente'] = trim($_POST['buscarcliente']);

    $producto  = $_POST['buscarproducto'];
    $cantidad  = (int) $_POST['ingresarcantidad'];

    // obtener datos del producto (ideal mover a service después)
   $datosproducto = ProductoRepository::getProductoVenta($conexion, $producto);

    $precio = $datosproducto['valor_unitario'] ?? 0;
    $color  = $datosproducto['color'] ?? '';

    if ($producto && $cantidad > 0 && $precio > 0) {
        VentaService::agregarProducto(
            $_SESSION['carrito'],
            $producto,
            $cantidad,
            $precio,
            $color
        );
    }
}

if (isset($_POST['eliminar'])) {
    VentaService::eliminarProducto($_SESSION['carrito'], $_POST['eliminar']);
}

if (isset($_POST['vaciar'])) {
    $_SESSION['carrito'] = VentaService::vaciarCarrito();
}
?>

<h2>Registrar Venta</h2>

<form method="POST" id="form_venta">

<!-- ================= CLIENTE ================= -->
<?php
$clientes = ClienteRepository::getAll($conexion);
?>

Cliente:
<select name="buscarcliente" required>
    <option value="">Selecciona un cliente</option>

    <?php foreach ($clientes as $c) { ?>
        <option value="<?= $c['rut'] ?>"
            <?= (isset($_SESSION['cliente']) && $_SESSION['cliente'] == $c['rut']) ? 'selected' : '' ?>>
            <?= $c['nombre'] . " " . $c['apellido'] . " (" . $c['rut'] . ")" ?>
            - Crédito: $<?= $c['credito'] ?>
        </option>
    <?php } ?>

</select>
<br><br>

<!-- ================= PRODUCTO ================= -->
<?php
$productos = ProductoRepository::getBySucursal($conexion,$_SESSION['sucursal']);

?>

Producto:
<select name="buscarproducto" required>
    <option value="">Selecciona producto</option>

    <?php foreach ($productos as $p) { ?>
        <option 

            value="<?= $p['id'] ?>"
            data-precio="<?= $p['valor_unitario'] ?>"
            data-color="<?= $p['color'] ?>"
        >
            <?= $p['producto'] ?> - <?= $p['color'] ?>
            (Stock: <?= $p['stock'] ?>) - $<?= $p['valor_unitario'] ?>
        </option>
    <?php } ?>

</select>

<input type="hidden" id="color_oculto" name="color_oculto">
<input type="hidden" id="precio_oculto" name="precio_oculto">
<br><br>

Cantidad:
<input type="number" name="ingresarcantidad" min="1" required>

<br><br>

<button type="submit" name="agregar">➕ Agregar al carrito</button>

</form>

<hr>

<h3>🛒 Carrito</h3>

<?php
$carrito = $_SESSION['carrito'];
$total = VentaService::calcularTotal($carrito);
?>

<?php if (!empty($carrito)) { ?>

<table bordered="1" cellpadding="5">
    <tr>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Precio</th>
        <th>Subtotal</th>
        <th>Color</th>
        <th>Acción</th>
    </tr>

    <?php foreach ($carrito as $i => $item) { 

        // nombre producto
        $stmt = $conexion->prepare("SELECT producto FROM producto WHERE id = ?");
        $stmt->bind_param("i", $item['producto']);
        $stmt->execute();
        $nombre = $stmt->get_result()->fetch_assoc()['producto'] ?? 'N/A';

        $subtotal = $item['cantidad'] * $item['precio'];
    ?>

    <tr>
        <td><?= $nombre ?></td>
        <td><?= $item['cantidad'] ?></td>
        <td>$<?= $item['precio'] ?></td>
        <td>$<?= $subtotal ?></td>
        <td><?= $item['color'] ?></td>
        <td>
            <form method="POST">
                <input type="hidden" name="eliminar" value="<?= $i ?>">
                <button type="submit">❌</button>
            </form>
        </td>
    </tr>

    <?php } ?>

</table>

<h4>Total: $<?= number_format($total, 0, ',', '.') ?></h4>

<!-- VACÍAR -->
<form method="POST" onsubmit="return confirm('¿Vaciar carrito?')">
    <button type="submit" name="vaciar">🧹 Vaciar carrito</button>
</form>

<!-- FINALIZAR -->
<form action="registroventas.php" method="POST">
    <button type="submit">💰 Finalizar Venta</button>
</form>

<?php } else { ?>
    <p>El carrito está vacío</p>
<?php } ?>