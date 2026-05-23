<?php

require_once __DIR__ . "/../services/ProductoService.php";

class ProductoController {

    public static function ejecutar($conexion, $data, $session) {

        $service = new ProductoService();

        return $service->crearProducto($conexion, $data, $session);
    }
}