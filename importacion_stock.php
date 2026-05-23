<?php
require 'conexion.php';

if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
    $file = fopen($_FILES['archivo']['tmp_name'], 'r');
    if (!$file) die("Error al abrir el archivo.");

    $filas_procesadas = 0;

    while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
        // Columnas del CSV
        $producto = trim($row[0]);
        $stock = (int)trim($row[1]);
        $valor_unitario = (int)trim($row[2]);
        $categoria_medicion = trim($row[3]);
        $color = trim($row[4]);

        // Validaciones
        $categorias_validas = ['kg','litro','unidad','metro'];
        if (!in_array($categoria_medicion, $categorias_validas)) $categoria_medicion = 'unidad';

        $colores_validos = ['rojo','azul','verde','negro','blanco','amarillo'];
        if (!in_array($color, $colores_validos)) $color = null;

        // Insertar o actualizar stock si ya existe
        $stmt = $conexion->prepare("
            INSERT INTO producto (producto, stock, valor_unitario, categoria_medicion, color, fecha_ingreso_stock)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                stock = stock + VALUES(stock),
                valor_unitario = VALUES(valor_unitario),
                categoria_medicion = VALUES(categoria_medicion),
                color = VALUES(color)
        ");
        $stmt->bind_param("siiss", $producto, $stock, $valor_unitario, $categoria_medicion, $color);
        if ($stmt->execute()) $filas_procesadas++;
        $stmt->close();
    }

    fclose($file);
    echo "Importación finalizada. Filas procesadas: $filas_procesadas";
} else {
    echo "No se recibió ningún archivo o hubo un error al subirlo.";
}
?>