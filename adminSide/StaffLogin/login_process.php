<?php require_once __DIR__ . '/../../common/bootstrap.php'; ?>
<?php
require_once __DIR__ . '/../../common/database.php';
post_only();
$id=number_id($_POST['account_id'] ?? '');
$password=text_input($_POST['password'] ?? '', 'password',72);
$row=one('SELECT a.password,s.* FROM Accounts a JOIN Staffs s ON s.account_id=a.account_id WHERE a.account_id=?',[$id]);
if (!$row || !password_verify($password,$row['password'])) problem('Invalid staff account or password.',401);
session_regenerate_id(true);
$_SESSION['logged_account_id']=$id; $_SESSION['logged_staff_id']=$row['staff_id']; $_SESSION['logged_staff_name']=$row['staff_name'];
redirect_to('../panel/pos-panel.php');
