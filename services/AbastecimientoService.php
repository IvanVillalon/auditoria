<?php

require_once __DIR__ . "/../repositories/AbastecimientoRepository.php";

class AbastecimientoService {

    public function procesar($conexion, $data, $session) {

        $repo = new AbastecimientoRepository();

        $id_producto = (int)$data['id_producto'];
        $destino = (int)$data['id_sucursal'];
        $cantidad = (int)$data['cantidad'];
        $color = trim($data['color']);
        $comentario = trim($data['comentario_abastecimiento']);

        $origen = 1; // bodega central
        $id_operario = $session['id'];

        if ($cantidad <= 0) {
            throw new Exception("Cantidad inválida");
        }

        $conexion->begin_transaction();

        try {

            /* =========================
               1. STOCK ORIGEN
            ========================= */
            $stockOrigen = $repo->getStock($conexion, $id_producto, $origen, $color);

            if ($stockOrigen < $cantidad) {
                throw new Exception("Stock insuficiente en bodega central");
            }

            $stockAnterior = $stockOrigen;

            /* =========================
               2. DESCONTAR ORIGEN
            ========================= */
            $repo->descontarStock($conexion, $id_producto, $origen, $cantidad, $color);

            /* =========================
               3. SUMAR DESTINO
            ========================= */
            $repo->sumarStockDestino($conexion, $id_producto, $destino, $cantidad, $color);

            $stockNuevo = $repo->getStock($conexion, $id_producto, $destino, $color);

            /* =========================
               4. ABSTECIMIENTO
            ========================= */
            $id_abastecimiento = $repo->insertAbastecimiento(
                $conexion,
                $id_producto,
                $origen,
                $destino,
                $cantidad,
                $color,
                $comentario,
                $id_operario
            );

            /* =========================
               5. MOVIMIENTO STOCK
            ========================= */
            $repo->insertMovimiento(
                $conexion,
                'abastecimiento',
                $id_producto,
                $destino,
                $cantidad,
                $stockAnterior,
                $stockNuevo,
                $id_abastecimiento,
                $comentario,
                $id_operario,
                $color
            );

            $conexion->commit();

            return [
                "status" => "ok",
                "mensaje" => "Abastecimiento realizado correctamente"
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