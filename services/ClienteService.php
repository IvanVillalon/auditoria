<?php

require_once __DIR__ . '/../repositories/ClienteRepository.php';

class ClienteService {

    public function registrar($conexion, $data, $session) {

        /* =========================
           LIMPIAR DATOS
        ========================= */

        $nombre = trim($data['nombre']);
        $apellido = trim($data['apellido']);
        $rut = trim($data['rut_cliente']);
        $correo = trim($data['correo']);

        /* =========================
           VALIDACIONES
        ========================= */

        if (
            $nombre === "" ||
            $apellido === "" ||
            $rut === "" ||
            $correo === ""
        ) {

            return [
                "status" => "error",
                "mensaje" => "Todos los campos son obligatorios"
            ];
        }

        /* =========================
           VALIDAR EMAIL
        ========================= */

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

            return [
                "status" => "error",
                "mensaje" => "Correo inválido"
            ];
        }

        /* =========================
           VALIDAR RUT DUPLICADO
        ========================= */

        if (ClienteRepository::existeRut($conexion, $rut)) {

            return [
                "status" => "error",
                "mensaje" => "El cliente ya existe"
            ];
        }

        /* =========================
           TRANSACCIÓN
        ========================= */

        $conexion->begin_transaction();

        try {

            ClienteRepository::insertarCliente(
                $conexion,
                $nombre,
                $apellido,
                $rut,
                $correo
            );

            $conexion->commit();

            return [
                "status" => "ok",
                "mensaje" => "Cliente registrado correctamente"
            ];

        } catch (Exception $e) {

            $conexion->rollback();

            return [
                "status" => "error",
                "mensaje" => $e->getMessage()
            ];
        }
    }
}