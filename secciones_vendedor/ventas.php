<?php

require_once 'core/db.php';
require_once 'core/auth.php';

require_once __DIR__ . '/../controllers/VentasController.php';
require_once __DIR__ . '/../repositories/ClienteRepository.php';
require_once __DIR__ . '/../repositories/ProductoRepository.php';
require_once __DIR__ . '/../services/CarritoService.php';

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

/* =========================
   ACCIONES (SOLO CONTROLLER)
========================= */

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['agregar'])) {
        VentasController::agregarProducto($conexion, $_SESSION, $_POST);
    }

    if (isset($_POST['eliminar'])) {
        VentasController::eliminar($_SESSION, $_POST);
    }

    if (isset($_POST['vaciar'])) {
        VentasController::vaciar($_SESSION);
    }

    if (isset($_POST['finalizar'])) {
        $ok = VentasController::finalizar($conexion, $_SESSION);

        if ($ok) {
            $_SESSION['carrito'] = [];
            $mensaje = "✔ Venta realizada correctamente";
        } else {
            $mensaje = "❌ Error al finalizar venta";
        }
    }
}

$carrito = $_SESSION['carrito'];

?>

<h2>Registrar Venta</h2>

<?php if (!empty($mensaje)) { ?>
    <p><?= $mensaje ?></p>
<?php } ?>

<hr>

<!-- ================= CLIENTE ================= -->
<?php $clientes = ClienteRepository::getAll($conexion); ?>

<form method="POST">

Cliente:
<select name="buscarcliente" required>
    <option value="">Selecciona cliente</option>

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
<?php $productos = ProductoRepository::getBySucursal($conexion, $_SESSION['sucursal']); ?>

Producto:
<select name="buscarproducto" required>
    <option value="">Selecciona producto</option>

    <?php foreach ($productos as $p) { ?>
        <option value="<?= $p['id'] ?>">
            <?= $p['producto'] ?> - <?= $p['color'] ?>
            (Stock: <?= $p['stock'] ?>) - $<?= $p['valor_unitario'] ?>
        </option>
    <?php } ?>

</select>

<br><br>

Cantidad:
<input type="number" name="ingresarcantidad" min="1" required>

<br><br>

<button type="submit" name="agregar">➕ Agregar al carrito</button>

</form>

<hr>

<h3>🛒 Carrito</h3>

<?php $total = CarritoService::total($carrito); ?>

<?php if (!empty($carrito)) { ?>

<table border="1" cellpadding="5">
    <tr>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Precio</th>
        <th>Subtotal</th>
        <th>Color</th>
        <th>Acción</th>
    </tr>

    <?php foreach ($carrito as $i => $item) { ?>

        <?php
            $producto = ProductoRepository::obtenerProductoporId($conexion, $item['producto']);
            $subtotal = $item['cantidad'] * $item['precio'];
        ?>

        <tr>
            <td><?= $producto['producto'] ?></td>
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

<!-- ================= ACCIONES ================= -->

<form method="POST" onsubmit="return confirm('¿Vaciar carrito?')">
    <button type="submit" name="vaciar">🧹 Vaciar carrito</button>
</form>

<br>

<form method="POST" onsubmit="return confirm('¿Finalizar venta?')">
    <button type="submit" name="finalizar">💰 Finalizar Venta</button>
</form>

<?php } else { ?>

<p>El carrito está vacío</p>

<?php } ?>