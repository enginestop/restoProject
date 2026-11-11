<?php
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';
$id=text_input($_GET['id'] ?? '', 'record ID',255);
if ($_SERVER['REQUEST_METHOD']==='POST') problem('Method not allowed.',405);
include __DIR__ . '/../inc/dashHeader.php';
?><div class="form-panel"><?php ui_heading('Confirm deletion','Manager credentials are required to continue.','Administration'); ?><section class="surface"><p class="info-note">Selected account: <strong><?= h($id) ?></strong>. Referenced transaction records cannot be deleted.</p><form method="post" action="deleteAccount.php"><?= csrf_field() ?><input type="hidden" name="id" value="<?= h($id) ?>"><div class="form-grid"><?php ui_field('admin_id','Manager account ID',['type'=>'number','min'=>1,'autocomplete'=>'username']); ui_field('password','Manager password',['type'=>'password','autocomplete'=>'current-password']); ?></div><?php ui_actions('Confirm deletion','../panel/account-panel.php','Back'); ?></form></section></div><?php include __DIR__ . '/../inc/dashFooter.php'; ?>
