<?php
require_once __DIR__ . '/operations.php';
post_only();
require_staff_role($link,['Manager']);
require_manager_password($link);
$id=$deleteKind==='menu' ? text_input($_POST['id'] ?? '', 'item ID',6) : number_id($_POST['id'] ?? '');
if ($deleteKind==='reservation') cancel_reservation($id);
elseif ($deleteKind==='bill') {
    transaction(function () use ($id) {
        lock_bill($id);
        if (one('SELECT bill_item_id FROM Bill_Items WHERE bill_id=?',[$id])) problem('A bill containing order items cannot be deleted. Remove its items first.',409);
        db('DELETE FROM Bills WHERE bill_id=?',[$id]);
    });
} else {
    $map=['account'=>['Accounts','account_id'],'staff'=>['Staffs','staff_id'],'customer'=>['Memberships','member_id'],'table'=>['Restaurant_Tables','table_id'],'menu'=>['Menu','item_id']];
    [$table,$key]=$map[$deleteKind];
    db("DELETE FROM $table WHERE $key=?",[$id]);
}
$panel=['reservation'=>'reservation','bill'=>'bill','account'=>'account','staff'=>'staff','customer'=>'customer','table'=>'table','menu'=>'menu'][$deleteKind];
redirect_to('/adminSide/panel/' . $panel . '-panel.php');
