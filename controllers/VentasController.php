<?php

require_once __DIR__ . '/../services/VentaService.php';
require_once __DIR__ . '/../repositories/ProductoRepository.php';
require_once __DIR__ . '/../services/CarritoService.php';
class VentasController
{
    public static function agregarProducto($conexion, &$session, $post)
    {
        $session['cliente'] = trim($post['buscarcliente']);
        $producto = (int) $post['buscarproducto'];
        $cantidad = (int) $post['ingresarcantidad'];

        $datos = ProductoRepository::getProductoVenta($conexion, $producto);

        if ($datos && $cantidad > 0) {

            VentaService::agregarProducto(
                $session['carrito'],
                $producto,
                $cantidad,
                $datos['valor_unitario'],
                $datos['color']
            );
        }
    }

    public static function eliminar(&$session, $post)
    {
        CarritoService::eliminar(
            $session['carrito'],
            $post['eliminar']
        );
    }

    public static function vaciar(&$session)
    {
        $session['carrito'] = CarritoService::vaciar();
    }
    public static function finalizar($conexion, &$session)
    {
        if (empty($session['carrito']) || empty($session['cliente'])) {
            return false;
        }
        return VentaService::finalizarVenta(
            $conexion,
            $_SESSION['carrito'],
            $_SESSION['cliente'],
            $_SESSION['sucursal'],
            $id_vendedor = $_SESSION['id']
        );
    }
}