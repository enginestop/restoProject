<?php
require_once __DIR__ . '/database.php';
function revenue_metrics(): array {
    $today=date('Y-m-d'); $tomorrow=date('Y-m-d',strtotime('+1 day'));
    $periods=['Today'=>[$today,$tomorrow],'This week'=>[date('Y-m-d',strtotime('monday this week')),$tomorrow],
        'This month'=>[date('Y-m-01'),$tomorrow], 'This year'=>[date('Y-01-01'),$tomorrow], 'All time'=>['1000-01-01','9999-12-31']];
    $rows=[];
    foreach ($periods as $label=>[$start,$end]) {
        $row=one('SELECT COALESCE(SUM(subtotal),0) subtotal,COALESCE(SUM(tax_amount),0) tax,COALESCE(SUM(total_amount),0) total FROM Bills WHERE payment_time>=? AND payment_time<?',[$start,$end]);
        $rows[]=[$label,$row['subtotal'],$row['tax'],$row['total']];
    }
    return $rows;
}
function sales_rows(?string $category=null, ?int $member=null, bool $allTime=false): array {
    $sql='SELECT bi.item_id, bi.item_name_snapshot AS item_name, bi.category_snapshot AS category, SUM(bi.quantity) AS quantity, SUM(bi.unit_price*bi.quantity) AS revenue FROM Bill_Items bi JOIN Bills b ON b.bill_id=bi.bill_id WHERE b.payment_time IS NOT NULL';
    $params=[];
    if (!$allTime) { $sql.=' AND b.payment_time>=? AND b.payment_time<?'; $params=[date('Y-m-01'),date('Y-m-01',strtotime('first day of next month'))]; }
    if ($category!==null) { $sql.=' AND bi.category_snapshot=?'; $params[]=$category; }
    if ($member!==null) { $sql.=' AND b.member_id=?'; $params[]=$member; }
    return db($sql.' GROUP BY bi.item_id,bi.item_name_snapshot,bi.category_snapshot ORDER BY quantity DESC,bi.item_id',$params)->fetch_all(MYSQLI_ASSOC);
}
function bar_chart(array $rows,string $label,string $value,string $title): string {
    if (!$rows) return '<div class="empty-state"><p>No paid transactions for this period.</p></div>';
    $rows=array_slice($rows,0,10); $max=max(array_map(fn($r)=>(float)$r[$value],$rows));
    $html='<div class="chart-bars" role="img" aria-label="'.h($title).'">';
    foreach ($rows as $row) {
        $width=$max>0?(float)$row[$value]/$max*100:0;
        $html.='<div class="chart-row"><div class="chart-label"><span>'.h($row[$label]).'</span><strong>'.h($row[$value]).'</strong></div><div class="chart-track"><span class="chart-fill" style="width:'.$width.'%"></span></div></div>';
    }
    return $html.'</div>';
}
