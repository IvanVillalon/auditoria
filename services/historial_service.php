<?php

if (session_status() === PHP_SESSION_NONE) {// verificamos si existe inicio de sesion
    session_start();
}
function registrarHistorial(
    $conexion,
    $id_usuario,
    $accion,
    $descripcion,
    $tabla,
    $id_registro = null
) {

    

    $id_sucursal = $_SESSION['sucursal'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // 🔥 convertir NULL correctamente para MySQLi
    if ($id_registro === null) {
        $id_registro = null;
    }

    if ($id_sucursal === '') {
        $id_sucursal = null;
    }

    $sql = $conexion->prepare("
        INSERT INTO historial_acciones
        (id_usuario, accion, descripcion, tabla_afectada, id_registro, id_sucursal, ip_usuario, fecha)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    if (!$sql) {
        die("Error en prepare: " . $conexion->error);
    }

    // 🔥 tipos correctos:
    // i = int
    // s = string
    $sql->bind_param(
        "isssiis",
        $id_usuario,
        $accion,
        $descripcion,
        $tabla,
        $id_registro,
        $id_sucursal,
        $ip
    );

    if (!$sql->execute()) {
        die("Error al registrar historial: " . $sql->error);
    }

    $sql->close();
}
?>