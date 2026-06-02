<?php

require_once __DIR__ . "/../services/ProductoService.php";

class ProductoController {

    public static function ejecutar($conexion, $data, $session) {

        $service = new ProductoService();

        return $service->crearProducto($conexion, $data, $session);
    }
    public static function obtenerProductos($conexion) {
        $service = new ProductoService();
        return $service->obtenerProductos($conexion);
    }
    public static function obtenerProductoPorId($conexion, $id) {

        $service = new ProductoService();

        return $service->obtenerProductoPorId(
            $conexion,
            $id
        );
    }  
    public static function actualizarProducto($conexion, $data, $session) {
       
        $service = new ProductoService();
        return $service->actualizarProducto($conexion, $data, $session); 
}
}