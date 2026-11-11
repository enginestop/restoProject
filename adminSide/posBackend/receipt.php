<?php
require_once __DIR__ . '/checkIfLoggedIn.php';
require_once __DIR__ . '/../../common/pdf.php';
$id=number_id($_GET['bill_id'] ?? '');
$bill=one('SELECT * FROM Bills WHERE bill_id=?',[$id]);
if (!$bill || $bill['payment_time']===null) problem('Paid bill not found.',404);
$pdf=new FPDF(); pdf_fonts($pdf); $pdf->AddPage();
$pdf->SetFont('Arial','B',12); $pdf->Cell(0,10,"Johnny's Dining & Bar - Receipt",0,1); $pdf->SetFont('Arial','',12);
foreach (['bill_id','table_id','staff_id','member_id','bill_time','payment_time','payment_method'] as $field) $pdf->MultiCell(0,8,pdf_text(ucwords(str_replace('_',' ',$field)).': '.($bill[$field] ?? '-')));
if ($bill['card_id']) { $card=one('SELECT card_last4 FROM card_payments WHERE card_id=?',[$bill['card_id']]); $pdf->MultiCell(0,8,'Card ending: '.$card['card_last4']); }
$pdf->Ln(5);
foreach (db('SELECT * FROM Bill_Items WHERE bill_id=?',[$id]) as $line) $pdf->MultiCell(0,8,pdf_text($line['item_name_snapshot'].' | '.$line['quantity'].' x RM '.$line['unit_price'].' = RM '.number_format($line['quantity']*$line['unit_price'],2)));
foreach (['subtotal','tax_amount','total_amount','tendered_amount','change_amount'] as $field) if ($bill[$field]!==null) $pdf->MultiCell(0,8,ucwords(str_replace('_',' ',$field)).': RM '.$bill[$field]);
$pdf->Output('D','Receipt-'.$id.'.pdf');
