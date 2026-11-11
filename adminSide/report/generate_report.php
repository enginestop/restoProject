<?php
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';
require_staff_role($link,['Manager']);
require_once __DIR__ . '/../../common/reporting.php';
require_once __DIR__ . '/../../common/pdf.php';
class RestaurantReport extends FPDF {
    function Header() { $this->SetFont('Arial','B',12); $this->Cell(0,10,"Johnny's Dining & Bar - Report",0,1); }
    function Footer() { $this->SetY(-15); $this->SetFont('Arial','',12); $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C'); }
    function paragraph(string $text) { $this->SetFont('Arial','',12); $this->MultiCell(0,8,iconv('UTF-8','windows-1252//TRANSLIT',$text)); }
}
$pdf=new RestaurantReport(); pdf_fonts($pdf); $pdf->AddPage();
$pdf->paragraph('Generated: '.date('Y-m-d H:i:s').' Asia/Kuala_Lumpur');
$pdf->paragraph('Paid transactions by payment date. Currency: RM.');
foreach (revenue_metrics() as [$label,$net,$tax,$total]) $pdf->paragraph("$label: Net $net | Tax $tax | Collected $total");
foreach (['day'=>"DATE(payment_time)",'week'=>"YEARWEEK(payment_time,3)",'month'=>"DATE_FORMAT(payment_time,'%Y-%m')",'year'=>"YEAR(payment_time)"] as $period=>$expression) {
    $pdf->AddPage(); $pdf->paragraph(ucfirst($period).' totals (last 30 periods)');
    foreach (db("SELECT $expression AS period,SUM(subtotal) AS net,SUM(tax_amount) AS tax,SUM(total_amount) AS total FROM Bills WHERE payment_time IS NOT NULL GROUP BY $expression ORDER BY period DESC LIMIT 30") as $row) $pdf->paragraph($row['period'].': Net '.$row['net'].' | Tax '.$row['tax'].' | Collected '.$row['total']);
}
$pdf->AddPage(); $pdf->paragraph('Net revenue by ordered category - all time');
foreach (db('SELECT bi.category_snapshot AS category,SUM(bi.quantity*bi.unit_price) AS revenue FROM Bill_Items bi JOIN Bills b ON b.bill_id=bi.bill_id WHERE b.payment_time IS NOT NULL GROUP BY bi.category_snapshot') as $row) $pdf->paragraph($row['category'].': RM '.$row['revenue']);
$pdf->AddPage(); $pdf->paragraph('Item sales this month');
$rows=sales_rows();
if (!$rows) $pdf->paragraph('No paid transactions for this period.');
foreach ($rows as $row) $pdf->paragraph($row['item_name'].' | '.$row['category'].' | '.$row['quantity'].' units | RM '.$row['revenue']);
$pdf->AddPage(); $pdf->paragraph('Items with no paid orders this month');
foreach (db('SELECT m.item_name FROM Menu m WHERE NOT EXISTS (SELECT 1 FROM Bill_Items bi JOIN Bills b ON bi.bill_id=b.bill_id WHERE bi.item_id=m.item_id AND b.payment_time>=? AND b.payment_time<?)',[date('Y-m-01'),date('Y-m-01',strtotime('first day of next month'))]) as $row) $pdf->paragraph($row['item_name']);
$pdf->AddPage(); $pdf->paragraph('Kitchen monthly completed quantity and average minutes');
foreach (db("SELECT DATE_FORMAT(time_ended,'%Y-%m') AS period,SUM(quantity) AS quantity,ROUND(AVG(TIMESTAMPDIFF(SECOND,time_submitted,time_ended))/60,2) AS minutes FROM Kitchen WHERE time_ended IS NOT NULL GROUP BY DATE_FORMAT(time_ended,'%Y-%m') ORDER BY period DESC") as $row) $pdf->paragraph($row['period'].' | '.$row['quantity'].' units | '.$row['minutes'].' minutes');
$pdf->Output('D','Restaurant-report.pdf');
