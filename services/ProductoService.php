<?php
require_once __DIR__ . '/../repositories/NuevoProductoRepository.php';
class ProductoService {

    public function crearProducto($conexion, $data, $session) {

        $repo = new NuevoProductoRepository();

        $nombre = trim($data['nombre']);
        $precio = (int)$data['precio'];
        $id_operario = $session['id'];
        $categoria_medicion = trim($data['categoria_medicion']);
        $categoria_producto = trim($data['categoria_producto']);

        if ($nombre === "" || $categoria_medicion === "" || $categoria_producto === "") {
            throw new Exception("Datos inválidos");
        }
      

        $conexion->begin_transaction();

        try {

            $id_producto = $repo->insertProducto($conexion, $nombre, $precio, $categoria_medicion, $categoria_producto, $id_operario);

            $conexion->commit();

            return [
                "status" => "ok",
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
}

?>