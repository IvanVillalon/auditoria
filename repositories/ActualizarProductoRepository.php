<?php
class ActualizarProductoRepository {

     public function obtenerProductos($conexion) {

        $sql = "
            SELECT 
                id,
                producto
            FROM producto
            ORDER BY producto ASC
        ";

        $resultado = $conexion->query($sql);

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }


    public function actualizarProducto($conexion, $id_producto, $nuevo_precio, $nueva_categoria_medicion, $nueva_categoria_producto) {
        $stmt = $conexion->prepare("
            UPDATE producto
            SET valor_unitario = ?, categoria_medicion = ?, categoria_producto = ?
            WHERE id = ?
        ");

        $stmt->bind_param("issi",  $nuevo_precio, $nueva_categoria_medicion, $nueva_categoria_producto, $id_producto);
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar el producto: " . $stmt->error);
        }
    }
     public function obtenerProductoPorId($conexion, $id) {

        $stmt = $conexion->prepare("
            SELECT 
                id,
                producto,
                valor_unitario,
                categoria_producto,
                categoria_medicion
            FROM producto
            WHERE id = ?
        ");

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $resultado = $stmt->get_result();

        return $resultado->fetch_assoc();
    }
}

?>