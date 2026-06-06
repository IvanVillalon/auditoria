<?php
require_once __DIR__ . '/core/db.php';
/*require_once 'services/historial_service.php';*/

session_start();

// 🔥 guardar datos ANTES de destruir sesión
$id_usuario = $_SESSION['id'] ?? null;
$id_sucursal = $_SESSION['sucursal'] ?? null;

/*if ($id_usuario) {

    // registrar logout
   $sql = $conexion->prepare("
        INSERT INTO historial_acciones
        (id_usuario, accion, descripcion, tabla_afectada, id_registro, id_sucursal, ip_usuario, fecha)
        VALUES (?, 'logout', 'Cierre de sesión', 'usuarios', NULL, ?, ?, NOW())
    ");

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    $sql->bind_param("iis", $id_usuario, $id_sucursal, $ip);
    $sql->execute()
}
*/
// 🔥 ahora sí destruir sesión
session_unset();
session_destroy();

header("Location: login.php");
exit();
?>