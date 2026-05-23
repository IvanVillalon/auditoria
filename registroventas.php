<?php
session_start();
include "conexion.php";
/*require_once "services/historial_service.php";*/

$id_vendedor = $_SESSION['id'] ?? null;
$id_sucursal = $_SESSION['sucursal'] ?? null;

if (!$id_vendedor) {
    echo "<script>alert('Usuario no autenticado'); window.location='login.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_SESSION['cliente'])) {
        echo "<script>alert('No se ha seleccionado un cliente'); window.location='vendedor.php';</script>";
        exit();
    }

    $rut_cliente = $_SESSION['cliente'] ?? null;
    if (!$rut_cliente){
        die("no ay rut cliente");
    }
    $stmtcredito= $conexion->prepare("SELECT credito FROM cliente WHERE rut = ?");
    $stmtcredito->bind_param("s", $rut_cliente);
    $stmtcredito->execute();
    $rescredito = $stmtcredito->get_result()->fetch_assoc();
    echo $rut_cliente;

    // 🔥 GENERAR NÚMERO FACTURA
    $stmt = $conexion->prepare("SELECT numero_factura FROM ventas ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($fila = $resultado->fetch_assoc()) {
        $ultimo = (int) str_replace('F-', '', $fila['numero_factura']);
        $nuevo_numero = $ultimo + 1;
    } else {
        $nuevo_numero = 1;
    }

    $numero_factura = "F-" . str_pad($nuevo_numero, 6, "0", STR_PAD_LEFT);

    try {

        $conexion->begin_transaction();

        // 🔥 AGRUPAR PRODUCTOS (CLAVE)
        $carritoAgrupado = [];

        foreach ($_SESSION['carrito'] as $item) {
            $clave = $item['producto'] . '_' . $item['color'];

            if (!isset($carritoAgrupado[$clave])) {
                $carritoAgrupado[$clave] = [
                    "producto" => $item['producto'],
                    "color" => $item['color'],
                    "cantidad" => 0,
                    "precio" => $item['precio']
                ];
            }

            $carritoAgrupado[$clave]['cantidad'] += $item['cantidad'];
        }

        // 🔥 VALIDAR STOCK
        $total = 0;

        foreach ($carritoAgrupado as $item) {

            $stmt = $conexion->prepare("
                SELECT stock 
                FROM inventario
                WHERE id_producto = ? AND id_sucursal = ? AND color = ?
            ");
            $stmt->bind_param("iis", $item['producto'], $id_sucursal, $item['color']);
            $stmt->execute();

            $res = $stmt->get_result();
            $fila = $res->fetch_assoc();

            $stock_actual = $fila['stock'] ?? 0;

            if ($stock_actual < $item['cantidad']) {
                $conexion->rollback();
                echo "<script>
                    alert('Stock insuficiente para producto {$item['producto']} color {$item['color']} (Stock: $stock_actual)');
                    window.location='vendedor.php';
                </script>";
                exit();
            }

            $total += $item['cantidad'] * $item['precio'];
            $credito = $rescredito['credito'] ?? 0;
            $totalFinal = $total - $credito;
            if ($totalFinal < 0) {
                $totalFinal = 0;
            }
            $nuevocredito = max(0, $credito - $total);
            $stmt = $conexion->prepare("UPDATE cliente SET credito = ? WHERE rut = ?");
            $stmt->bind_param("ds", $nuevocredito, $rut_cliente);
            $stmt->execute();
        }

        // 🔥 INSERTAR VENTA
        $estado = "completa";
        var_dump($rut_cliente);
        echo "BD actual: " . $conexion->query("SELECT DATABASE()")->fetch_row()[0];
        var_dump($_SESSION['cliente']);
        $stmt = $conexion->prepare("
            INSERT INTO ventas (numero_factura, rut_cliente, id_vendedor, id_sucursal, estado, total) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssiisi", $numero_factura, $rut_cliente, $id_vendedor, $id_sucursal, $estado, $total);
        $stmt->execute();

        $id_venta = $conexion->insert_id;
        
        /*registrarHistorial($conexion,$_SESSION['id'],'Venta','Registro venta','ventas', $id_venta);*/
        

        // 🔥 INSERTAR DETALLE + DESCONTAR STOCK
        foreach ($carritoAgrupado as $item) {

            // detalle venta
            $stmt2 = $conexion->prepare("
                INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio, color, credito_usado, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?,?)
            ");
            $stmt2->bind_param("iiidsii", $id_venta, $item['producto'], $item['cantidad'], $item['precio'], $item['color'], $credito, $subtotal);
             $subtotal = $item['cantidad'] * $item['precio'];
            $stmt2->execute();

            // actualizar stock CON COLOR
            $stmt3 = $conexion->prepare("
                UPDATE inventario 
                SET stock = stock - ? 
                WHERE id_producto = ? AND id_sucursal = ? AND color = ?
            ");
            $stmt3->bind_param("iiis", $item['cantidad'], $item['producto'], $id_sucursal, $item['color']);
            $stmt3->execute();
        }

        $conexion->commit();

        unset($_SESSION['carrito']);
echo "<script>
    alert('Venta registrada correctamente');
    window.location.href='boleta.php?id_venta=$id_venta';
</script>";
exit();
    } catch (Exception $e) {
        $conexion->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>


