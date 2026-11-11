<?php
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';
require_staff_role($link,['Manager']);
include __DIR__ . '/../inc/dashHeader.php';
?>
<div class="form-panel"><?php ui_heading('Create new table','Set the number of guests this table can accommodate.','Administration'); ?><section class="surface"><form method="post" action="succ_create_table.php"><?= csrf_field() ?><div class="form-grid"><?php ui_field('capacity','Capacity',['type'=>'number','min'=>1,'max'=>1000,'placeholder'=>'Number of guests','hint'=>'Enter a whole number from 1 to 1000.','full'=>true]); ?></div><?php ui_actions('Create table','../panel/table-panel.php'); ?></form></section></div><?php include __DIR__ . '/../inc/dashFooter.php'; ?>
