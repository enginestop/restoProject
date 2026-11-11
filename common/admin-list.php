<?php
require_once __DIR__ . '/../adminSide/posBackend/checkIfLoggedIn.php';
$definitions = [
 'account'=>['title'=>'Account Details','description'=>'Review account information and find the account you need.','table'=>'Accounts','order'=>'account_id','search'=>['email','account_id'],'columns'=>['account_id'=>'Account ID','email'=>'Email','register_date'=>'Registered','phone_number'=>'Phone number'],'add'=>[['Add Staff','staffCrud/createStaff.php'],['Add Memberships','customerCrud/createCust.php'],['Create Account','accountCrud/createStaffAccount.php']]],
 'customer'=>['title'=>'Membership Details','description'=>'Find members and review their points and linked accounts.','table'=>'Memberships','order'=>'member_id','search'=>['member_name','member_id'],'columns'=>['member_id'=>'Member ID','member_name'=>'Member name','points'=>'Points','account_id'=>'Account ID'],'add'=>[['Add Membership','customerCrud/createCust.php']]],
 'staff'=>['title'=>'Staff Details','description'=>'Review staff roles and the accounts connected to each team member.','table'=>'Staffs','order'=>'account_id','search'=>['staff_name','staff_id'],'columns'=>['staff_id'=>'Staff ID','staff_name'=>'Staff name','role'=>'Role','account_id'=>'Account ID'],'add'=>[['Add Staff','staffCrud/createStaff.php'],['Link Account','staffCrud/linkStaff.php']]],
 'table'=>['title'=>'Table Details','description'=>'Manage table capacity. Open Point of Sale to check active bills.','table'=>'Restaurant_Tables','order'=>'table_id','search'=>['table_id','capacity'],'columns'=>['table_id'=>'Table ID','capacity'=>'Capacity','is_available'=>'Availability'],'add'=>[['Add Table','tableCrud/createTable.php']]],
 'menu'=>['title'=>'Menu Details','description'=>'Review menu categories, item descriptions and current prices.','table'=>'Menu','order'=>'item_id','search'=>['item_type','item_category','item_name','item_id'],'columns'=>['item_id'=>'Item ID','item_name'=>'Item name','item_type'=>'Type','item_category'=>'Category','item_price'=>'Price (RM)','item_description'=>'Description'],'add'=>[['Add Item','menuCrud/createItem.php']]],
 'bill'=>['title'=>'Bill Details','description'=>'Review bill history and download receipts for completed payments.','table'=>'Bills','order'=>'bill_id','search'=>['table_id','payment_method','bill_id','card_id'],'columns'=>['bill_id'=>'Bill ID','staff_id'=>'Staff ID','member_id'=>'Member ID','reservation_id'=>'Reservation ID','table_id'=>'Table ID','card_id'=>'Card ID','payment_method'=>'Payment method','bill_time'=>'Opened at','payment_time'=>'Paid at'],'add'=>[]],
 'reservation'=>['title'=>'Reservation Details','description'=>'Find upcoming and historical reservations and view their receipts.','table'=>'Reservations','order'=>'reservation_date DESC, reservation_time DESC','search'=>['reservation_date','reservation_id','customer_name'],'columns'=>['reservation_id'=>'Reservation ID','customer_name'=>'Customer name','table_id'=>'Table ID','reservation_time'=>'Time','reservation_date'=>'Date','head_count'=>'Guests','special_request'=>'Special request'],'add'=>[['Add Reservation','reservationsCrud/createReservation.php']]],
];
$definition=$definitions[$listKind];
$search=text_input($_POST['search'] ?? $_GET['search'] ?? '', 'search',255,false);
$sql='SELECT ' . ($listKind==='account' ? 'account_id,email,register_date,phone_number' : '*') . ' FROM '.$definition['table'];
$params=[];
if ($search!=='') {
    $sql.=' WHERE '.implode(' OR ',array_map(fn($column)=>$column.' LIKE ?', $definition['search']));
    $params=array_fill(0,count($definition['search']),'%'.$search.'%');
}
$rows=db($sql.' ORDER BY '.$definition['order'],$params)->fetch_all(MYSQLI_ASSOC);
include __DIR__ . '/../adminSide/inc/dashHeader.php';
?>
<div class="page-header"><?php ui_heading($definition['title'],$definition['description'],'Restaurant management'); ?><div class="toolbar-actions"><?php foreach($definition['add'] as [$label,$path]): ?><a class="btn <?= $path===$definition['add'][0][1]?'btn-primary':'btn-secondary' ?>" href="/adminSide/<?= h($path) ?>"><?= h($label) ?></a><?php endforeach; ?></div></div>
<form method="post" class="toolbar"><?= csrf_field() ?><?php ui_field('search','Search records',['value'=>$search,'required'=>false,'maxlength'=>255,'placeholder'=>['account'=>'Account ID or email','customer'=>'Member ID or name','staff'=>'Staff ID or name','table'=>'Table ID or capacity','menu'=>'Item ID or name','bill'=>'Bill or table ID','reservation'=>'ID, name or date'][$listKind]]); ?><div class="toolbar-actions"><button class="btn btn-primary" type="submit">Search</button><a class="btn btn-secondary" href="<?= h($_SERVER['SCRIPT_NAME']) ?>">Show All</a></div></form>
<?php
if (!$rows) ui_empty('No records were found.','Try a different search or use Show All to view every record.');
else {
    $headings=array_values($definition['columns']);
    if(in_array($listKind,['menu','bill','reservation'],true)) $headings[]='Actions';
    if($listKind==='reservation') $headings[]='Status';
    ui_table_start($headings,$definition['title']);
    foreach($rows as $row) {
        echo '<tr>';
        foreach($definition['columns'] as $field=>$label) {
            $value=$row[$field] ?? null;
            if($field==='is_available') $value=$value?'Available':'Unavailable';
            elseif($field==='capacity') $value.=' guests';
            elseif($field==='item_price') $value=number_format((float)$value,2);
            elseif($field==='payment_method' && $value) $value=ucfirst($value);
            echo '<td>'.h($value===null || $value==='' ? '—' : $value).'</td>';
        }
        if($listKind==='menu') echo '<td><a class="btn btn-secondary btn-small" href="../menuCrud/updateItemVerify.php?id='.urlencode($row['item_id']).'">Edit</a></td>';
        elseif($listKind==='bill') echo '<td>'.($row['payment_time'] ? '<a class="btn btn-secondary btn-small" href="../posBackend/receipt.php?bill_id='.(int)$row['bill_id'].'">Receipt</a>' : '<span class="status-badge status-badge--pending">Unpaid</span>').'</td>';
        elseif($listKind==='reservation') {
            echo '<td><div class="table-actions"><a class="btn btn-secondary btn-small" href="../reservationsCrud/reservationReceipt.php?reservation_id='.(int)$row['reservation_id'].'">Receipt</a>';
            if(!$row['cancelled_at'] && $managerUi) echo '<a class="btn btn-danger btn-small" href="../reservationsCrud/deleteReservationVerify.php?id='.(int)$row['reservation_id'].'">Cancel</a>';
            echo '</div></td><td><span class="status-badge'.($row['cancelled_at']?' status-badge--muted':'').'">'.($row['cancelled_at']?'Cancelled':'Active').'</span></td>';
        }
        echo '</tr>';
    }
    ui_table_end();
    echo '<p class="table-count">'.count($rows).' record'.(count($rows)===1?'':'s').' shown. Wide tables can be scrolled horizontally.</p>';
}
include __DIR__ . '/../adminSide/inc/dashFooter.php';
