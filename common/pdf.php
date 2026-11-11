<?php
require_once __DIR__ . '/../adminSide/posBackend/fpdf186/fpdf.php';
function pdf_fonts(FPDF $pdf): void {
    $pdf->AddFont('Arial','','arial.php');
    $pdf->AddFont('Arial','B','arialbd.php');
    $pdf->AddFont('Arial','I','ariali.php');
    $pdf->SetFont('Arial','',12);
}
function pdf_text($value): string { return iconv('UTF-8','windows-1252//TRANSLIT',(string)$value); }
