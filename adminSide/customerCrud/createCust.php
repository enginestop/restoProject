<?php
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';
require_staff_role($link,['Manager']);
include __DIR__ . '/../inc/dashHeader.php';
?>
<div class="form-panel"><?php ui_heading('Create new membership','Create the customer account and set the starting points.','Administration'); ?><section class="surface"><form method="post" action="success_createMembership.php"><?= csrf_field() ?><div class="form-grid"><?php ui_field('member_name','Member name',['autocomplete'=>'name','maxlength'=>255]); ui_field('points','Starting points',['type'=>'number','min'=>0,'max'=>10000000,'value'=>'0']); ?><?php
ui_field('email','Email',['type'=>'email','autocomplete'=>'email','maxlength'=>255]);
ui_field('register_date','Registration date',['type'=>'date','value'=>date('Y-m-d'),'max'=>date('Y-m-d')]);
ui_field('phone_number','Phone number',['type'=>'tel','autocomplete'=>'tel','maxlength'=>30,'placeholder'=>'+60123456789']);
ui_field('password','Password',['type'=>'password','autocomplete'=>'new-password','minlength'=>6,'maxlength'=>72,'hint'=>'Use at least 6 characters.']);
?></div><?php ui_actions('Create Membership','../panel/customer-panel.php'); ?></form></section></div><?php include __DIR__ . '/../inc/dashFooter.php'; ?>
