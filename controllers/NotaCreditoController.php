<?php

require_once __DIR__ . "/../services/NotaCreditoService.php";

class NotaCreditoController {

    public static function crear($conexion, $data, $session) {

        // 1. Validación básica de sesión
        if (!isset($session['id']) || !isset($session['sucursal'])) {
            return [
                "status" => "error",
                "mensaje" => "Sesión inválida"
            ];
        }

        // 2. Validación de datos mínimos
        if (!isset($data['id_venta']) || empty($data['productos'])) {
            return [
                "status" => "error",
                "mensaje" => "Datos incompletos"
            ];
        }

        try {

            $service = new NotaCreditoService();

            return $service->procesar($conexion, $data, $session);

        } catch (Exception $e) {

            return [
                "status" => "error",
                "mensaje" => $e->getMessage()
            ];
        }
    }
    public static function index($conexion,$session,$get){ 
        $service = new NotaCreditoService();
        return $service->listarVentas(
            $conexion,
            $session,
            $get
        );  
        
    }
}

?>