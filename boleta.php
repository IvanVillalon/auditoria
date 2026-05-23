<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once __DIR__ . '/fpdf/fpdf.php';
require 'conexion.php';

$id_venta = $_GET['id_venta'] ?? 0;

if (!$id_venta) {
    die("No se recibió el id de venta");
}

# 🔥 ENCABEZADO VENTA
$stmt = $conexion->prepare("
    SELECT v.numero_factura, v.total, v.id, v.fecha, dv.id_venta, 
           c.nombre, c.apellido, c.rut
    FROM ventas v
    INNER JOIN cliente c ON v.rut_cliente = c.rut
    INNER JOIN detalle_venta dv ON v.id = dv.id_venta
    WHERE v.id = ?
");
$stmt->bind_param("i", $id_venta);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();

if (!$venta) {
    ob_end_clean();
    die("Venta no encontrada");
}

# 🔥 DETALLE VENTA
$stmt2 = $conexion->prepare("
    SELECT p.producto, d.cantidad, d.precio, d.color,
           (d.cantidad * d.precio) AS subtotal
    FROM detalle_venta d
    INNER JOIN producto p ON p.id = d.id_producto
    WHERE d.id_venta = ?
");
$stmt2->bind_param("i", $id_venta);
$stmt2->execute();
$detalle = $stmt2->get_result();

# 🔥 PDF
$pdf = new FPDF();
$pdf->AddPage();

/* ================= HEADER ================= */
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'BOLETA DE VENTA',0,1,'C');

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,'Variedades Full 2000',0,1);
$pdf->Cell(0,8,'Sucursal: ' . $_SESSION['sucursal_nombre'],0,1);
$pdf->Cell(0,8,'Factura: '.$venta['numero_factura'],0,1);
$pdf->Cell(0,8,'Fecha: '.$venta['fecha'],0,1);
$pdf->Cell(0,8,'Cliente: '.$venta['nombre'].' '.$venta['apellido'],0,1);
$pdf->Cell(0,8,'RUT: '.$venta['rut'],0,1);

$pdf->Ln(5);

/* ================= TABLA ================= */
$pdf->SetFont('Arial','B',10);
$pdf->Cell(60,10,'Producto',1);
$pdf->Cell(25,10,'Cant.',1);
$pdf->Cell(30,10,'Precio',1);
$pdf->Cell(30,10,'Color',1);
$pdf->Cell(35,10,'Subtotal',1);
$pdf->Ln();

$pdf->SetFont('Arial','',10);

$totalCalculado = 0;

while ($row = $detalle->fetch_assoc()) {

    $pdf->Cell(60,10,$row['producto'],1);
    $pdf->Cell(25,10,$row['cantidad'],1);
    $pdf->Cell(30,10,'$'.$row['precio'],1);
    $pdf->Cell(30,10,$row['color'],1);
    $pdf->Cell(35,10,'$'.$row['subtotal'],1);
    $pdf->Ln();

    $totalCalculado += $row['subtotal'];
}

/* ================= TOTAL ================= */
$pdf->Ln(5);
$pdf->SetFont('Arial','B',12);

$pdf->Cell(145,10,'TOTAL:',1);
$pdf->Cell(35,10,'$'.$totalCalculado,1);

/* ================= OUTPUT ================= */
ob_end_clean();
$pdf->Output();
exit;
?>