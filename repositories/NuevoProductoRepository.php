<?php

class NuevoProductoRepository {

  
    public function generarCodigo($conexion) {

        $stmt = $conexion->prepare("
            SELECT id
            FROM producto 
            ORDER BY id DESC 
            LIMIT 1
        ");


        $stmt->execute();
        $result = $stmt->get_result();

        $lastId = 0;

        if ($row = $result->fetch_assoc()) {
            $lastId = (int)$row['id'];
        }

        $nextId = $lastId + 1;

        return "PROD-" . str_pad($nextId, 7, "0", STR_PAD_LEFT);
    }

    public function insertProducto($conexion, $nombre, $precio, $categoria_medicion, $categoria_producto, $id_operario) {



        $codigo = $this->generarCodigo($conexion);

        $stmt = $conexion->prepare("
            INSERT INTO producto 
            (producto, codigo, valor_unitario, categoria_medicion, categoria_producto, id_operario)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssissi",
            $nombre,
            $codigo,
            $precio,
            $categoria_medicion,
            $categoria_producto,
            $id_operario
        );

        $stmt->execute();

        return $stmt->insert_id;
    

   

    
    }
    public function obtenerProductoActualizar($conexion){
        $stmt = $conexion->prepare("SELECT id, producto FROM producto ORDER BY producto ASC");
        $stmt->execute();   
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }   

}
?>