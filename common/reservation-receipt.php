<?php
require_once __DIR__ . '/operations.php';
require_once __DIR__ . '/pdf.php';
$row = one('SELECT * FROM Reservations WHERE reservation_id=?', [number_id($_GET['reservation_id'] ?? '')]);
if (!$row) problem('Reservation not found.', 404);
if (!($staffReceipt ?? false) && !reservation_owner($row)) problem('You do not have access to this reservation.', 403);
$pdf = new FPDF(); pdf_fonts($pdf);
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0,10,"Johnny's Dining & Bar - Reservation",0,1);
$pdf->SetFont('Arial','',12);
foreach (['reservation_id','customer_name','table_id','reservation_time','reservation_date','head_count','special_request','cancelled_at'] as $field) {
    $text = ucwords(str_replace('_',' ',$field)) . ': ' . ($row[$field] ?? '-');
    $pdf->MultiCell(0,8,iconv('UTF-8','windows-1252//TRANSLIT', $text));
}
$pdf->Output('D', 'Reservation-' . $row['reservation_id'] . '.pdf');
