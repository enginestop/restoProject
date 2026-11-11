<?php
require_once __DIR__ . '/../../common/ui.php';
if(isset($_SESSION['logged_account_id'])) redirect_to('../panel/pos-panel.php');
ui_start('Staff login','Sign in with your staff account ID to access the restaurant workspace.');
?><form method="post" action="login_process.php"><?= csrf_field() ?><div class="form-stack"><?php ui_field('account_id','Staff Account ID',['type'=>'number','min'=>1,'autocomplete'=>'username']); ui_field('password','Password',['type'=>'password','autocomplete'=>'current-password']); ?></div><?php ui_actions('Login','/','Home'); ?></form><p class="auth-footnote">Customer account? <a href="/customerSide/customerLogin/login.php">Customer login</a></p><?php ui_end(); ?>
