<?php
require_once __DIR__ . '/../../common/ui.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
foreach (['loggedin','account_id','email','member_name'] as $key) unset($_SESSION[$key]);
session_regenerate_id(true);
redirect_to('login.php');
}
ui_start('Log out','Confirm when you are ready to end this session.');
?><form method="post"><?= csrf_field() ?><?php ui_actions('Log out','/customerSide/customerLogin/profile.php','Stay signed in'); ?></form><?php ui_end(); ?>
