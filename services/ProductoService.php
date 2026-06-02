<?php
require_once __DIR__ . '/../repositories/NuevoProductoRepository.php';
require_once __DIR__ . '/../repositories/ActualizarProductoRepository.php';
class ProductoService {

    public function crearProducto($conexion, $data, $session) {

        $repo = new NuevoProductoRepository();

        $nombre = trim($data['nombre'] ?? '');
        $precio = (int)($data['precio'] ?? 0);
        $id_operario = $session['id'];
        $categoria_medicion = trim($data['categoria_medicion'] ?? '');
        $categoria_producto = trim($data['categoria_producto'] ?? '');
        if ($nombre === "" || $categoria_medicion === "" || $categoria_producto === "") {
            throw new Exception("Datos inválidos");
        }
      

        $conexion->begin_transaction();

        try {

            $id_producto = $repo->insertProducto($conexion, $nombre, $precio, $categoria_medicion, $categoria_producto, $id_operario);

            $conexion->commit();

            return [
                "status" => "success",
                "mensaje" => "Producto creado correctamente"
            ];

        } catch (Exception $e) {

            $conexion->rollback();

            return [
                "status" => "error",
                "mensaje" => $e->getMessage()
            ];
        }
    }
    public function obtenerProductos($conexion) {
        $repo = new ActualizarProductoRepository();
        return $repo->obtenerProductos($conexion);
    }
    public function obtenerProductoporId($conexion, $id) {
        $repo = new ActualizarProductoRepository();
        return $repo->obtenerProductoPorId($conexion, $id);
    }
    public function actualizarProducto($conexion, $data, $session) {
        $repo = new ActualizarProductoRepository();

        $id_producto = (int)($data['id_producto'] ?? 0);
        $nuevo_precio = (int)($data['nuevo_precio'] ?? 0);
        $nueva_categoria_medicion = trim($data['nueva_categoria_medicion'] ?? '');
        $nueva_categoria_producto = trim($data['nueva_categoria_producto'] ?? '');

        if ($id_producto <= 0 || $nueva_categoria_medicion === "" || $nueva_categoria_producto === "") {
            throw new Exception("Datos inválidos");
        }

        $conexion->begin_transaction();

        try {

            $repo->actualizarProducto($conexion, $id_producto, $nuevo_precio, $nueva_categoria_medicion, $nueva_categoria_producto);

            $conexion->commit();

            return [
                "status" => "success",
                "mensaje" => "Producto actualizado correctamente"
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

?>