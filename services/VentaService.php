<?php

require_once __DIR__ . '/../repositories/VentasRepository.php';
require_once __DIR__ . '/../repositories/ProductoRepository.php';
class VentaService {
    public static function generarNumeroFactura($conexion){
        $query= $conexion->query("SELECT numero_factura FROM ventas ORDER BY id DESC LIMIT 1");
        $ultima = $query->fetch_assoc();
        //primera factura
        if(!$ultima){
            return "F-000001";
        }
        $numeroActual =$ultima['numero_factura'];
        $numero= str_replace("F-", "", $numeroActual);
        //convertir a int
        $numero = (int) $numero;
        // sumar 
        $numero++;
        //formatear con ceros
        return "F-" . str_pad($numero, 6, "0", STR_PAD_LEFT);

    }
    public static function agregarProducto(&$carrito, $producto, $cantidad, $precio, $color) {
        $carrito[] = [
            "producto" => $producto,
            "cantidad" => $cantidad,
            "precio" => $precio,
            "color" => $color
        ];
    }

    public static function eliminarProducto(&$carrito, $index) {
        unset($carrito[$index]);
        $carrito = array_values($carrito);
    }

    public static function calcularTotal($carrito) {
        $total = 0;

        foreach ($carrito as $item) {
            $total += $item['cantidad'] * $item['precio'];
        }

        return $total;
    }

    public static function vaciarCarrito() {
        return [];
    }
    public static function finalizarVenta($conexion, $carrito, $cliente, $sucursal, $id_vendedor)
    {
        $ventasRepo = new VentasRepository();
        $productoRepo = new ProductoRepository();

        $conexion->begin_transaction();

        try {

            // 1. crear venta
            $total = self::calcularTotal($carrito);
            $numero_factura = self::generarNumeroFactura($conexion);
            $venta_id = $ventasRepo->crearVenta($conexion, $cliente, $numero_factura, $sucursal, $total, $id_vendedor);

            foreach ($carrito as $item) {
                $subtotal = $item['cantidad'] * $item['precio'];
                // 2. insertar detalle
                $ventasRepo->crearDetalle(
                    $conexion,
                    $venta_id,
                    $item['producto'],
                    $item['cantidad'],
                    $item['precio'],
                    $subtotal,
                    $item['color']
                );

                // 3. descontar stock
                $productoRepo->descontarStock(
                    $conexion,
                    $item['producto'],
                    $item['cantidad'],
                    $sucursal,
                );
            }

            $conexion->commit();
            return true;

        } catch (Exception $e) {

            $conexion->rollback();
            die ($e->getMessage());
        }   
    }
}
?>