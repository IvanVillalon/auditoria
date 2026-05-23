<?php

//EL REPOSITORY REPRESENTA TODAS LAS ACCIONES QUE SE HACEN CON UN OBJETO, EN ESTE CASO, CLIENTE
// POR LO CUAL SE CREA UN SOLO REPOSITORY PARA TODAS LAS ACCIONES SOBRE UN CLIENTE

class ClienteRepository {

    /* =========================
       CONSULTAS
    ========================= */

    public static function getAll($conexion) {

        $stmt = $conexion->prepare("
            SELECT nombre, apellido, rut, credito
            FROM cliente
        ");

        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function getByRut($conexion, $rut) {

        $stmt = $conexion->prepare("
            SELECT *
            FROM cliente
            WHERE rut = ?
        ");

        $stmt->bind_param("s", $rut);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    /* =========================
       VALIDACIONES
    ========================= */

    public static function existeRut($conexion, $rut) {

        $stmt = $conexion->prepare("
            SELECT rut
            FROM cliente
            WHERE rut = ?
        ");

        $stmt->bind_param("s", $rut);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    /* =========================
       ACCIONES
    ========================= */

    public static function insertarCliente(
        $conexion,
        $nombre,
        $apellido,
        $rut,
        $correo
    ) {

        $stmt = $conexion->prepare("
            INSERT INTO cliente
            (nombre, apellido, rut, correo)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssss",
            $nombre,
            $apellido,
            $rut,
            $correo
        );

        $stmt->execute();

        return $stmt->insert_id;
    }
}
?>