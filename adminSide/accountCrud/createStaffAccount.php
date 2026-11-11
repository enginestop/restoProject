<?php
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';
require_staff_role($link,['Manager']);
include __DIR__ . '/../inc/dashHeader.php';
?>
<div class="form-panel"><?php ui_heading('Create new account','Create an account before linking it to a staff record.','Administration'); ?><section class="surface"><form method="post" action="success_create_staff_account.php"><?= csrf_field() ?><div class="form-grid"><?php
ui_field('email','Email',['type'=>'email','autocomplete'=>'email','maxlength'=>255]);
ui_field('register_date','Registration date',['type'=>'date','value'=>date('Y-m-d'),'max'=>date('Y-m-d')]);
ui_field('phone_number','Phone number',['type'=>'tel','autocomplete'=>'tel','maxlength'=>30,'placeholder'=>'+60123456789']);
ui_field('password','Password',['type'=>'password','autocomplete'=>'new-password','minlength'=>6,'maxlength'=>72,'hint'=>'Use at least 6 characters.']);
?></div><?php ui_actions('Create Account','../panel/account-panel.php'); ?></form></section></div><?php include __DIR__ . '/../inc/dashFooter.php'; ?>
