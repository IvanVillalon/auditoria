<?php
class VentaService {

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
}
?>