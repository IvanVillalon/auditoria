<?php

require_once __DIR__ . '/../services/VentaService.php';
require_once __DIR__ . '/../repositories/ProductoRepository.php';
require_once __DIR__ . '/../repositories/VentasRepository.php';
require_once __DIR__ . '/../services/CarritoService.php';
class VentasController
{
    public static function agregarProducto($conexion, &$session, $post)
    {
        $session['cliente'] = trim($post['buscarcliente']);
        $producto = (int) $post['buscarproducto'];
        $cantidad = (int) $post['ingresarcantidad'];

        $datos = ProductoRepository::getProductoVenta($conexion, $producto);
        if (!$datos || $cantidad <= 0){
            return ['ok' => false, 'mensaje' => 'Producto no encontrado o cantidad inválida'];
        }
        //validamos stock   
        $repo = new VentasRepository();
        $stock = $repo->getStockDisponible(
            $conexion,
            $producto,
            $session['sucursal'],
            $datos['color']
        );
        if ($cantidad > $stock) {
            return ['ok' => false, 'mensaje' => "Stock insuficiente. Disponible: $stock"];
        }

            VentaService::agregarProducto(
                $session['carrito'],
                $producto,
                $cantidad,
                $datos['valor_unitario'],
                $datos['color']
            );
            return ['ok' => true, 'mensaje' => 'Producto agregado al carrito'];
        
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
    try {
        $id_venta = VentaService::finalizarVenta(
            $conexion,
            $session['carrito']  ?? [],
            $session['cliente']  ?? '',
            $session['sucursal'] ?? 0,
            $session['id']       ?? 0
        );

        $session['carrito'] = [];

        return ['ok' => true, 'id_venta' => $id_venta, 'mensaje' => ''];

    } catch (Exception $e) {
        return ['ok' => false, 'id_venta' => null, 'mensaje' => $e->getMessage()];
    }
}
}  