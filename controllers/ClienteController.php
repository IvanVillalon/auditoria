<?php

require_once __DIR__ . '/../services/ClienteService.php';

class ClienteController {

    public static function ejecutar($conexion, $data, $session) {

        $service = new ClienteService();

        return $service->registrar(
            $conexion,
            $data,
            $session
        );
    }
}