<?php

require_once __DIR__ . "/../services/AbastecimientoService.php";

class AbastecimientoController {

    public static function ejecutar($conexion, $post, $session) {

        $service = new AbastecimientoService();
        return $service->procesar($conexion, $post, $session);
    }
}
