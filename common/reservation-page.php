<?php
require_once __DIR__ . '/operations.php';
$_SESSION['reservation_owner'] ??= bin2hex(random_bytes(32));
$date = $_GET['reservation_date'] ?? date('Y-m-d', strtotime('+1 day'));
$time = $_GET['reservation_time'] ?? '13:00:00';
$count = $_GET['head_count'] ?? '1';
[$date, $time, $head] = reservation_input(['reservation_date'=>$date, 'reservation_time'=>$time, 'head_count'=>$count]);
$tables = db('SELECT t.* FROM Restaurant_Tables t WHERE t.is_available=1 AND t.capacity>=? AND NOT EXISTS (SELECT 1 FROM Reservations r WHERE r.table_id=t.table_id AND r.reservation_date=? AND r.reservation_time=? AND r.cancelled_at IS NULL) ORDER BY t.table_id', [$head, $date, $time]);

require_once __DIR__ . '/ui.php';
$staffReservation=str_starts_with($_SERVER['SCRIPT_NAME'] ?? '', '/adminSide/');
if($staffReservation) { include __DIR__ . '/../adminSide/inc/dashHeader.php'; ui_heading('Reserve a table','Choose a time and table, then enter the reservation details.','Reservations'); }
else ui_start('Reserve a table','Choose a time and table, then enter your reservation details.',true);
$times=[];for($hour=10;$hour<=20;$hour++){ $slot=sprintf('%02d:00:00',$hour);$times[$slot]=substr($slot,0,5); }
?>
<section class="surface reservation-search"><h2>Find a table</h2><form method="get"><div class="form-grid"><?php
ui_field('reservation_date','Date',['id'=>'search_date','type'=>'date','value'=>$date,'min'=>date('Y-m-d'),'max'=>date('Y-m-d',strtotime('+1 year'))]);
ui_field('reservation_time','Time',['id'=>'search_time','choices'=>$times,'value'=>$time]);
ui_field('head_count','People',['id'=>'search_count','type'=>'number','min'=>1,'max'=>1000,'value'=>$head]);
?></div><div class="form-actions"><button class="btn btn-primary">Search</button></div></form></section>
<div class="reservation-grid"><section class="surface"><h2>Make reservation</h2><form method="post" action="insertReservation.php"><?= csrf_field() ?><div class="form-stack"><?php ui_field('customer_name','Customer name',['autocomplete'=>'name','maxlength'=>255]); ?>
<div class="field"><label for="table_id">Available table</label><select class="form-control" id="table_id" name="table_id" required><option value="">Select a table</option><?php $tableCount=0;foreach($tables as $table): $tableCount++; ?><option value="<?= h($table['table_id']) ?>">Table <?= h($table['table_id']) ?> · capacity <?= h($table['capacity']) ?></option><?php endforeach; ?></select><?php if(!$tableCount): ?><p class="field-help">No tables match this search. Try another time or guest count.</p><?php endif; ?></div>
<?php ui_field('special_request','Special request',['type'=>'textarea','maxlength'=>255,'required'=>false,'hint'=>'Optional. Let us know about any requirements.']); ?></div><input type="hidden" name="reservation_date" value="<?= h($date) ?>"><input type="hidden" name="reservation_time" value="<?= h($time) ?>"><input type="hidden" name="head_count" value="<?= h($head) ?>"><div class="form-actions"><button class="btn btn-primary" <?= !$tableCount?'disabled':'' ?>>Make reservation</button></div></form></section>
<aside class="surface"><h2>Selected visit</h2><dl class="definition-list"><div><dt>Date</dt><dd><?= h($date) ?></dd></div><div><dt>Time</dt><dd><?= h(substr($time,0,5)) ?></dd></div><div><dt>Guests</dt><dd><?= h($head) ?></dd></div></dl><p class="info-note section-block">After reserving, save the PDF receipt and bring your reservation ID when you arrive.</p></aside></div>
<?php if(!$staffReservation): $own=db('SELECT * FROM Reservations WHERE account_id=? OR owner_token=? ORDER BY reservation_id DESC',[$_SESSION['account_id'] ?? 0,$_SESSION['reservation_owner']])->fetch_all(MYSQLI_ASSOC); ?>
<section class="section-block"><h2>Your reservations</h2><?php if(!$own) ui_empty('No reservations yet.','Reservations made in this session or while signed in will appear here.'); else { ui_table_start(['Reservation ID','Date','Time','Table','Status','Actions'],'Your reservations'); foreach($own as $row): ?><tr><td><?= h($row['reservation_id']) ?></td><td><?= h($row['reservation_date']) ?></td><td><?= h($row['reservation_time']) ?></td><td><?= h($row['table_id']) ?></td><td><span class="status-badge <?= $row['cancelled_at']?'status-badge--muted':'' ?>"><?= $row['cancelled_at']?'Cancelled':'Active' ?></span></td><td><?php if(!$row['cancelled_at']): ?><div class="table-actions"><a class="btn btn-secondary btn-small" href="reservationReceipt.php?reservation_id=<?= h($row['reservation_id']) ?>">Receipt</a><form method="post" action="/customerSide/CustomerReservation/cancelReservation.php"><?= csrf_field() ?><input type="hidden" name="reservation_id" value="<?= h($row['reservation_id']) ?>"><button class="btn btn-danger btn-small">Cancel</button></form></div><?php else: ?><span class="field-help">No action required</span><?php endif; ?></td></tr><?php endforeach; ui_table_end(); } ?></section>
<?php endif; if($staffReservation) include __DIR__ . '/../adminSide/inc/dashFooter.php'; else ui_end(); ?>
