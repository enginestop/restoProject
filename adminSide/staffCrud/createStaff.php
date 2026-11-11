<?php
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';
require_staff_role($link,['Manager']);
include __DIR__ . '/../inc/dashHeader.php';
?>
<div class="form-panel"><?php ui_heading('Create new staff','Create the staff account and choose a role.','Administration'); ?><section class="surface"><form method="post" action="succ_create_staff.php"><?= csrf_field() ?><div class="form-grid"><?php ui_field('staff_name','Staff name',['autocomplete'=>'name','maxlength'=>255]); ui_field('role','Role',['choices'=>['Waiter','Chef','Manager']]); ?><?php
ui_field('email','Email',['type'=>'email','autocomplete'=>'email','maxlength'=>255]);
ui_field('register_date','Registration date',['type'=>'date','value'=>date('Y-m-d'),'max'=>date('Y-m-d')]);
ui_field('phone_number','Phone number',['type'=>'tel','autocomplete'=>'tel','maxlength'=>30,'placeholder'=>'+60123456789']);
ui_field('password','Password',['type'=>'password','autocomplete'=>'new-password','minlength'=>6,'maxlength'=>72,'hint'=>'Use at least 6 characters.']);
?></div><?php ui_actions('Create Staff','../panel/staff-panel.php'); ?></form></section></div><?php include __DIR__ . '/../inc/dashFooter.php'; ?>
