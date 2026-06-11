<?php

require_once __DIR__ . "/../repositories/NotaCreditoRepository.php";

class NotaCreditoService {

    public function procesar($conexion, $data, $session) {

        $repo = new NotaCreditoRepository();

        $id_venta   = (int)$data['id_venta'];
        $productos  = $data['productos'];
        $comentario = trim($data['comentario']);
        $tipo       = $data['tipo'];  // "devolucion", "ajuste_precio"

        // estado de la nota: "parcial", "completa"
        // en ajuste siempre es "completa", en devolución viene del JS
        $estado = ($tipo === "ajuste_precio") 
            ? "completa" 
            : ($data['estado'] ?? "parcial");

        if (!$id_venta || empty($productos)) {
            throw new Exception("Datos incompletos");
        }

        if ($comentario === "") {
            throw new Exception("Comentario obligatorio");
        }

        $venta = $repo->getVentaDetalle($conexion, $id_venta);

        if (!$venta) {
            throw new Exception("Venta no encontrada");
        }

        $rut_cliente = $venta[0]['rut_cliente'];

        $mapa = [];
        foreach ($venta as $v) {
            $key = $v['id_producto'] . "_" . $v['color'];
            $mapa[$key] = $v['cantidad'];
        }
        $monto_ajuste = 0;
        foreach ($productos as $p) {
            $key      = $p['id_producto'] . "_" . $p['color'];
            $tipoNota = $p['tipo_nota'] ?? 'devolucion';

            if (!isset($mapa[$key])) {
                throw new Exception("Producto no pertenece a la venta");
            }

            if ($tipoNota !== 'ajuste_precio') {
                if ($p['cantidad'] > $mapa[$key]) {
                    throw new Exception("No puedes devolver más de lo vendido");
                }
            }

            if ($tipoNota === 'ajuste_precio') {
                $monto_ajuste += ($p['precio']-($p['nuevo_precio'] ?? $p['precio']))*$p['cantidad'];
                if (empty($p['nuevo_precio']) || $p['nuevo_precio'] <= 0) {
                    throw new Exception("Debes ingresar un precio válido");
                }
                if ($p['nuevo_precio'] >= $p['precio']) {
                    throw new Exception("El nuevo precio debe ser menor al original");
                }
            }else{
                $monto_ajuste += $p['precio'] * $p['cantidad'];
            }
        }

        // ✅ Crear nota con estado y tipo correctos
        $id_nota = $repo->crearNota(
            $conexion,
            $id_venta,
            $tipo,      // "devolucion", "ajuste_precio"
            $estado,    // "parcial", "completa"
            $comentario,
            $session['id'],
            $monto_ajuste
        );

        $total_credito = 0;

        foreach ($productos as $p) {
            $tipoNota      = $p['tipo_nota'] ?? 'devolucion';
            $estadoProducto = $p['estado']   ?? '';
            $p['id_venta'] = $id_venta;

            if ($tipoNota === 'devolucion') {
                if (empty($estadoProducto)) {
                    throw new Exception("Debes seleccionar el estado del producto");
                }

                $repo->insertarDetalleNota($conexion, $id_nota, $p, $session['id']);

                if ($estadoProducto === 'danado') {
                    $repo->registrarDanado($conexion, $p, $session);
                }

                if ($estadoProducto === 'bueno') {
                    $repo->sumarStock($conexion, $p, $session);
                }

                $total_credito += $p['precio'] * $p['cantidad'];

            } else if ($tipoNota === 'ajuste_precio') {
                $repo->insertarDetalleNota($conexion, $id_nota, $p, $session['id']);
                $repo->ajustarPrecio($conexion, $p, $session);

                $diferencia     = ($p['precio'] - ($p['nuevo_precio'] ?? $p['precio'])) * $p['cantidad'];
                $total_credito += $diferencia;
            }
        }

        $repo->sumarCreditoCliente($conexion, $rut_cliente, $total_credito);

        // ✅ Anular venta solo si es devolución total
        if ($tipo === 'devolucion' && $estado === 'completa') {
            $repo->marcarVentaAnulada($conexion, $id_venta);
        }

        return [
            "status"          => "ok",
            "mensaje"         => "Nota de crédito registrada",
            "id_nota_credito" => $id_nota,
            "pdf"             => "comprobante_nota_credito.php?id_nota=" . $id_nota
        ];
    }

    public function listarVentas($conexion, $session, $get) {
        $limite = 10;
        $pagina = isset($get['pagina']) ? (int)$get['pagina'] : 1;
        $inicio = ($pagina - 1) * $limite;

        $facturas = NotaCreditoRepository::getVentasPaginadas(
            $conexion,
            $session['sucursal'],
            $inicio,
            $limite
        );

        $total = NotaCreditoRepository::totalVentas($conexion);

        return [
            'facturas'      => $facturas,
            'pagina'        => $pagina,
            'total_paginas' => ceil($total / $limite)
        ];
    }
}