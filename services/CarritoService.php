<?php

class CarritoService
{
    public static function agregar(&$carrito, $producto_id, $cantidad, $precio, $color)
    {
        // si ya existe el producto, solo suma cantidad
        foreach ($carrito as &$item) {
            if ($item['producto'] == $producto_id) {
                $item['cantidad'] += $cantidad;
                return;
            }
        }

        // si no existe, lo agrega
        $carrito[] = [
            'producto' => $producto_id,
            'cantidad' => $cantidad,
            'precio' => $precio,
            'color' => $color
        ];
    }

    public static function eliminar(&$carrito, $index)
    {
        unset($carrito[$index]);
        $carrito = array_values($carrito);
    }

    public static function vaciar()
    {
        return [];
    }

    public static function total($carrito)
    {
        $total = 0;

        foreach ($carrito as $item) {
            $total += $item['cantidad'] * $item['precio'];
        }

        return $total;
    }
}