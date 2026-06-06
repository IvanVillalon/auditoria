<?php
ob_start();
session_start();
require_once __DIR__ . '/fpdf/fpdf.php';
require_once __DIR__ . '/core/db.php';

$id_nota_credito = $_GET['id_nota'] ?? 0;

if (!$id_nota_credito) {
    die("No se recibió el id de nota de crédito");
}

$stmt = $conexion->prepare("
SELECT 
nc.id_nota_credito,
nc.fecha,
nc.estado,
nc.comentario,
dnc.cantidad,
dnc.precio,
dnc.subtotal,
dnc.color,
dnc.estado_producto,
p.producto,
p.codigo
FROM nota_credito nc
INNER JOIN detalle_nota_credito dnc 
    ON nc.id_nota_credito = dnc.id_nota_credito
INNER JOIN producto p 
    ON dnc.id_producto = p.id
WHERE nc.id_nota_credito = ?
");

$stmt->bind_param("i", $id_nota_credito);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    ob_end_clean();
    die("Nota de crédito no encontrada");
}

/* 🔥 Guardar todo en array */
$detalle = [];
while ($fila = $resultado->fetch_assoc()) {
    $detalle[] = $fila;
}

/* 🔥 Crear PDF */
$pdf = new FPDF();
$pdf->AddPage();

/* =======================
   CABECERA
======================= */
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'NOTA DE CREDITO',0,1,'C');

$pdf->Ln(3);

$pdf->SetFont('Arial','',12);

$fila = $detalle[0];

$pdf->Cell(0,8,'Numero Nota: ' . $fila['id_nota_credito'],0,1);
$pdf->Cell(0,8,'Fecha: ' . $fila['fecha'],0,1);
$pdf->Cell(0,8,'Estado: ' . $fila['estado'],0,1);
$pdf->Cell(0,8,'Comentario: ' . $fila['comentario'],0,1);

$pdf->Ln(5);

/* =======================
   TABLA DETALLE
======================= */
$pdf->SetFont('Arial','B',10);

$pdf->Cell(35,8,'Producto',1);
$pdf->Cell(25,8,'Codigo',1);
$pdf->Cell(20,8,'Cant.',1);
$pdf->Cell(25,8,'Precio',1);
$pdf->Cell(25,8,'Subtotal',1);
$pdf->Cell(25,8,'Color',1);
$pdf->Cell(25,8,'Estado',1);

$pdf->Ln();

$pdf->SetFont('Arial','',9);

foreach ($detalle as $fila) {

    $pdf->Cell(35,8,$fila['producto'],1);
    $pdf->Cell(25,8,$fila['codigo'],1);
    $pdf->Cell(20,8,$fila['cantidad'],1);
    $pdf->Cell(25,8,'$'.number_format($fila['precio'],0,',','.'),1);
    $pdf->Cell(25,8,'$'.number_format($fila['subtotal'],0,',','.'),1);
    $pdf->Cell(25,8,$fila['color'],1);
    $pdf->Cell(25,8,$fila['estado_producto'],1);

    $pdf->Ln();
}

/* =======================
   OUTPUT FINAL
======================= */
ob_end_clean();
$pdf->Output();
exit;
?>