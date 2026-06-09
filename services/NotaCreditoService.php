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
        $rut_cliente = $venta[0]['rut_cliente'];

        $mapa = [];
        foreach ($venta as $v) {
            $key = $v['id_producto'] . "_" . $v['color'];
            $mapa[$key] = $v['cantidad'];
        }

        // validar productos
        foreach ($productos as $p) {
            $key = $p['id_producto'] . "_" . $p['color'];

            if (!isset($mapa[$key])) {
                throw new Exception("Producto no pertenece a la venta");
            }

            if ($p['cantidad'] > $mapa[$key]) {
                throw new Exception("No puedes devolver más de lo vendido");
            }
        }

        // crear nota
        $id_nota = $repo->crearNota(
            $conexion,
            $id_venta,
            $tipo,
            $comentario,
            $session['id']
        );
        $total_credito = 0;
        // procesar productos
        foreach ($productos as $p) {

            // ✅ leer tipo_nota y estado correctamente
            $tipoNota = $p['tipo_nota'] ?? 'devolucion';
            $estado   = $p['estado']    ?? '';
            $p['id_venta'] = $id_venta;

            if ($tipoNota === 'devolucion') {
                // Solo en devolución se valida el estado
                if (empty($estado)) {
                    throw new Exception("Debes seleccionar el estado del producto");
                }
                $repo->insertarDetalleNota($conexion,$id_nota, $p, $session['id']);

                if ($estado === 'danado') {
                    $repo->registrarDanado($conexion, $p, $session);
                }

                if ($estado === 'bueno') {
                    $repo->sumarStock($conexion, $p, $session);
                }
                $total_credito += $p['precio'] * $p['cantidad'];
                // marcar venta anulada si es total
                }else if ($tipoNota === "ajuste_precio") {
                    $repo->insertarDetalleNota($conexion, $id_nota, $p, $session['id']);
                    $repo->ajustarPrecio($conexion, $p, $session);

                // Crédito = diferencia entre precio original y nuevo
                $diferencia = ($p['precio'] - ($p['nuevo_precio'] ?? $p['precio'])) * $p['cantidad'];
                $total_credito += $diferencia;
                }
                }
                
                $repo->sumarCreditoCliente($conexion,$rut_cliente,$total_credito);

                if ($tipo === "total"){
                    $repo->marcarVentaAnulada($conexion, $id_venta);
                }

                return [
                "status"         => "ok",
                "mensaje"        => "Nota de crédito registrada",
                "id_nota_credito" => $id_nota,
                "pdf"            => "comprobante_nota_credito.php?id_nota=" . $id_nota
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

/*class NotaCreditoService {

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
        $tipoNota = $p['tipo_nota'] ?? 'devolucion';
        $estado = $p['estado'] ?? '';
        if ($tipoNota === 'devolucion'){
            if(empty($estado)){
                throw new Exception("Debes seleccionar el estado del producto");
            }
            if ($estado === 'danado'){
                $repo->registrarDanado($conexion, $p, $session);
            }
            if ($estado === 'bueno'){
                $repo->sumarStock($conexion,$p, $session);
            }
        }
        if ($tipoNota === 'ajuste_precio'){
            $repo->ajustarPrecio($conexion, $p, $session);
        }
        if ($tipoNota === 'ajuste_descuento'){
            $repo->aplicarDescuento($conexion, $p, $session);
        }
        
        // procesar productos
        foreach ($productos as $p) {

            $repo->insertarDetalleNota(
                $conexion,
                $id_nota,
                $p,
                $session['id']
            );

            // 🔥 lógica negocio aquí (decisión)
            if ($p['.estado_producto'] === 'danado') {
                $repo->registrarDanado($conexion, $p, $session);
            }

            if ($p['.estado_producto'] === 'bueno') {
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
    public function listarVentas($conexion,$session,$get){ 
        $limite = 10;
        $pagina = isset($get['pagina'])
            ? (int) $get['pagina']
            : 1;
            $inicio= ($pagina - 1) * $limite;
            $facturas= NotaCreditoRepository::getVentasPaginadas(
                $conexion,
                $session['sucursal'],
                $inicio,
                $limite
            );
            $total= NotaCreditoRepository::totalVentas($conexion);
            return [
                'facturas' => $facturas,
                'pagina' => $pagina,
                'total_paginas' => ceil($total / $limite)
            ];
    }
}*/