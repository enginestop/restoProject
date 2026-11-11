<?php
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';
require_staff_role($link,['Manager']);
include __DIR__ . '/../inc/dashHeader.php';
?>
<div class="form-panel"><?php ui_heading('Create new menu item','Add the item details, category and price.','Administration'); ?><section class="surface"><form method="post" action="success_create.php"><?= csrf_field() ?><div class="form-grid"><?php
ui_field('item_id','Item ID',['maxlength'=>6,'pattern'=>'[A-Za-z0-9]{1,6}','hint'=>'1 to 6 letters or numbers.']);
ui_field('item_name','Item name',['maxlength'=>255]);
ui_field('item_type','Item type',['maxlength'=>255,'placeholder'=>'For example, Pasta']);
ui_field('item_category','Category',['choices'=>['Main Dishes','Side Snacks','Drinks']]);
ui_field('item_price','Price (RM)',['type'=>'number','min'=>'0.01','step'=>'0.01','placeholder'=>'0.00']);
ui_field('item_description','Description',['type'=>'textarea','required'=>false,'maxlength'=>255,'full'=>true]);
?></div><?php ui_actions('Create Item','../panel/menu-panel.php'); ?></form></section></div><?php include __DIR__ . '/../inc/dashFooter.php'; ?>
