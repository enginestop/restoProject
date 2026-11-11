<?php
require_once __DIR__ . '/accounts.php';
require_once __DIR__ . '/operations.php';
require_staff_role($link,['Manager']);
post_only();
switch ($mutation) {
    case 'account': case 'member': case 'staff':
        create_identity($_POST,$mutation);
        $panel = ['account'=>'account','member'=>'customer','staff'=>'staff'][$mutation];
        redirect_to('/adminSide/panel/' . $panel . '-panel.php');
    case 'table':
        $capacity=number_id($_POST['capacity'] ?? '', 'capacity');
        if ($capacity>1000) problem('Capacity must be between 1 and 1000.');
        db('INSERT INTO Restaurant_Tables (capacity,is_available) VALUES (?,1)',[$capacity]);
        redirect_to('/adminSide/panel/table-panel.php');
    case 'menu':
        db('INSERT INTO Menu (item_id,item_name,item_type,item_category,item_price,item_description) VALUES (?,?,?,?,?,?)', menu_input($_POST));
        redirect_to('/adminSide/panel/menu-panel.php');
    case 'link':
        $account=number_id($_POST['account_id'] ?? ''); $staff=number_id($_POST['staff_id'] ?? '');
        transaction(function () use ($account,$staff) {
            if (!one('SELECT account_id FROM Accounts WHERE account_id=? FOR UPDATE',[$account])) problem('Account not found.',404);
            $row=one('SELECT * FROM Staffs WHERE staff_id=? FOR UPDATE',[$staff]);
            if (!$row) problem('Staff not found.',404);
            if ($row['account_id'] !== null || one('SELECT staff_id FROM Staffs WHERE account_id=?',[$account])) problem('Account or staff is already linked.',409);
            db('UPDATE Staffs SET account_id=? WHERE staff_id=?',[$account,$staff]);
        });
        redirect_to('/adminSide/panel/staff-panel.php');
    default: problem('Unknown operation.',404);
}
