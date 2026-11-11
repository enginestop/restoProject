<?php
require_once __DIR__ . '/operations.php';
$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$billId = number_id($input['bill_id'] ?? '', 'bill');
$bill = one('SELECT * FROM Bills WHERE bill_id=?', [$billId]);
if (!$bill) problem('Bill not found.', 404);
$member = optional_id($input['member_id'] ?? null);
$reservation = optional_id($input['reservation_id'] ?? null);
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff = current_staff($link);
    $result = pay_bill($billId, (int)$staff['staff_id'], $member, $reservation, $paymentMethod, $_POST);
    $bill = one('SELECT * FROM Bills WHERE bill_id=?', [$billId]);
}
$totals = bill_totals($billId);
include __DIR__ . '/../adminSide/inc/dashHeader.php';
?><div class="page-header"><?php ui_heading($paymentMethod==='cash'?'Cash payment':'Card payment simulation','Review the bill and complete the payment.','Bill '.$billId); ?><a class="btn btn-secondary" href="posTable.php">Back to tables</a></div><div class="checkout-grid"><section class="surface"><div class="payment-title"><h2>Bill <?= h($billId) ?></h2><span class="status-badge <?= $bill['payment_time']===null?'status-badge--pending':'' ?>"><?= $bill['payment_time']===null?'Awaiting payment':'Paid' ?></span></div>
<?php $lines=db('SELECT * FROM Bill_Items WHERE bill_id=?',[$billId])->fetch_all(MYSQLI_ASSOC); if(!$lines) ui_empty('No items in this bill.'); else { ui_table_start(['Item','Price (RM)','Quantity','Total (RM)'],'Payment bill items'); foreach($lines as $line): ?><tr><td><?= h($line['item_name_snapshot']) ?></td><td><?= h($line['unit_price']) ?></td><td><?= h($line['quantity']) ?></td><td><?= money(cents($line['unit_price'])*(int)$line['quantity']) ?></td></tr><?php endforeach; ui_table_end(); } ?><ul class="summary-list"><li><span>Subtotal</span><span>RM <?= money($totals['subtotal']) ?></span></li><li><span>Tax (10%)</span><span>RM <?= money($totals['tax']) ?></span></li><li><span>Grand total</span><span>RM <?= money($totals['total']) ?></span></li></ul></section><section class="surface">
<?php if($bill['payment_time']!==null): ?><div class="success-symbol" aria-hidden="true">&#10003;</div><h2>Payment completed</h2><div class="alert alert-success" role="status"><?= $result && !$result['already_paid']?'Payment successful!':'Bill has already been paid.' ?></div><?php if($bill['payment_method']==='cash'): ?><p>Change is RM<?= h($bill['change_amount']) ?></p><?php endif; ?><div class="form-actions"><a class="btn btn-primary" href="receipt.php?bill_id=<?= h($billId) ?>">Print receipt</a><a class="btn btn-secondary" href="posTable.php">Back to tables</a></div>
<?php elseif($totals['total']>0): ?><h2><?= $paymentMethod==='cash'?'Cash received':'Card details' ?></h2><?php if($paymentMethod==='card'): ?><p class="info-note">Simulation only. No charge is sent to a payment provider. Only the last four card digits are retained.</p><?php endif; ?><form method="post"><?= csrf_field() ?><input type="hidden" name="bill_id" value="<?= h($billId) ?>"><input type="hidden" name="member_id" value="<?= h($member) ?>"><input type="hidden" name="reservation_id" value="<?= h($reservation) ?>"><div class="form-stack"><?php if($paymentMethod==='cash') ui_field('payment_amount','Payment amount (RM)',['type'=>'number','min'=>'0','step'=>'0.01','placeholder'=>'0.00']); else {
ui_field('cardName','Account holder name',['maxlength'=>255,'autocomplete'=>'cc-name']);
ui_field('cardNumber','Card number',['inputmode'=>'numeric','pattern'=>'[0-9]{15,19}','maxlength'=>19,'autocomplete'=>'cc-number']);
ui_field('expiryDate','Expiry date',['placeholder'=>'MM/YYYY','pattern'=>'(0[1-9]|1[0-2])/[0-9]{4}','autocomplete'=>'cc-exp']);
ui_field('securityCode','Security code',['type'=>'password','inputmode'=>'numeric','pattern'=>'[0-9]{3,4}','maxlength'=>4,'autocomplete'=>'cc-csc','hint'=>'Validated for this simulation and never stored.']);
} ?></div><?php ui_actions('Pay','orderItem.php?bill_id='.$billId.'&table_id='.$bill['table_id'],'Back to bill'); ?></form><?php else: ui_empty('Cannot pay an empty bill.','Add items in Point of Sale first.'); endif; ?></section></div><?php include __DIR__ . '/../adminSide/inc/dashFooter.php'; ?>
