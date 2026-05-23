
<?php
session_start();
require 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'auditor') {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Acceso denegado"
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {

        $input = json_decode(file_get_contents("php://input"), true);

        $id_referencia = $input['id_referencia'] ?? null;
        $accion = $input['accion'] ?? null;
        $motivo_alerta = trim($input['motivo_alerta'] ?? '');
        $prioridad = strtolower($input['prioridad'] ?? 'media');
        $tipo_reporte = $input['tipo_reporte'] ?? null;

        $estado = "pendiente";
        $descripcion = $motivo_alerta;

        if (!$id_referencia || !$accion || !$tipo_reporte) {
            echo json_encode([
                "status" => "error",
                "mensaje" => "Datos incompletos"
            ]);
            exit();
        }

        $tiposPermitidos = [
            'nota_credito',
            'venta',
            'devolucion',
            'cliente',
            'stock',
            'conteo'
        ];

        if (!in_array($tipo_reporte, $tiposPermitidos)) {
            echo json_encode([
                "status" => "error",
                "mensaje" => "Tipo de reporte inválido"
            ]);
            exit();
        }

        switch ($accion) {

            case 'solicitardetalle':
                $titulo = "Solicitud de detalle - " . ucfirst(str_replace("_", " ", $tipo_reporte));
                break;

            case 'devolucion':
                $titulo = "Solicitud de devolución - " . ucfirst(str_replace("_", " ", $tipo_reporte));
                break;

            case 'alertar':
                $titulo = "Alerta sobre " . ucfirst(str_replace("_", " ", $tipo_reporte));
                break;
            case 'conteo':
                $titulo = "Alerta sobre " . ucfirst(str_replace("_", " ", $tipo_reporte));
                break;

            default:
                echo json_encode([
                    "status" => "error",
                    "mensaje" => "Acción inválida"
                ]);
                exit();
        }

        $sql = $conexion->prepare("
            INSERT INTO reportes_auditor
            (
                id_auditor,
                tipo_reporte,
                id_referencia,
                titulo,
                descripcion,
                prioridad,
                estado
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $sql->bind_param(
            "isissss",
            $_SESSION["id"],
            $tipo_reporte,
            $id_referencia,
            $titulo,
            $descripcion,
            $prioridad,
            $estado
        );

        if ($sql->execute()) {

            echo json_encode([
                "status" => "success",
                "mensaje" => "Reporte creado correctamente"
            ]);

        } else {

            echo json_encode([
                "status" => "error",
                "mensaje" => "Error al crear reporte"
            ]);
        }

        $sql->close();

    } catch (Exception $e) {

        echo json_encode([
            "status" => "error",
            "mensaje" => $e->getMessage()
        ]);
    }
}
?>