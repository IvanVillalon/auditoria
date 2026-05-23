<?php

require_once __DIR__ . "/../repositories/NotaCreditoRepository.php";

class NotaCreditoService {

    public function procesar($conexion, $data, $session) {

        $repo = new NotaCreditoRepository();

        $id_venta   = (int)$data['id_venta'];
        $productos  = $data['productos'];
        $comentario = trim($data['comentario']);
        $tipo       = $data['tipo'];

        if (!$id_venta || empty($productos)) {
            throw new Exception("Datos incompletos");
        }

        if ($comentario === "") {
            throw new Exception("Comentario obligatorio");
        }

        // validar venta y productos originales
        $venta = $repo->getVentaDetalle($conexion, $id_venta);

        if (!$venta) {
            throw new Exception("Venta no encontrada");
        }

        $mapa = [];

        foreach ($venta as $v) {
            $key = $v['id_producto'] . "_" . $v['color'];
            $mapa[$key] = $v['cantidad'];
        }

        $total_credito = 0;

        // validar devolución
        foreach ($productos as $p) {

            $key = $p['id_producto'] . "_" . $p['color'];

            if (!isset($mapa[$key])) {
                throw new Exception("Producto no pertenece a la venta");
            }

            if ($p['cantidad'] > $mapa[$key]) {
                throw new Exception("No puedes devolver más de lo vendido");
            }

            $total_credito += $p['precio'] * $p['cantidad'];
        }

        // crear nota
        $id_nota = $repo->crearNota(
            $conexion,
            $id_venta,
            $tipo,
            $comentario,
            $session['id']
        );

        // procesar productos
        foreach ($productos as $p) {

            $repo->insertarDetalleNota(
                $conexion,
                $id_nota,
                $p,
                $session['id']
            );

            // 🔥 lógica negocio aquí (decisión)
            if ($p['estado'] === 'danado') {
                $repo->registrarDanado($conexion, $p, $session);
            }

            if ($p['estado'] === 'bueno') {
                $repo->sumarStock($conexion, $p, $session);
            }
        }

        // actualizar cliente
        $repo->sumarCreditoCliente(
            $conexion,
            $id_venta,
            $total_credito
        );

        // actualizar venta si es total
        if ($tipo === "total") {
            $repo->marcarVentaAnulada($conexion, $id_venta);
        }

        return [
            "status" => "ok",
            "mensaje" => "Nota de crédito registrada",
            "id_nota_credito" => $id_nota,
            "pdf" => "comprobante_nota_credito.php?id_nota=" . $id_nota
        ];
    }
}