<?php
require_once __DIR__ . '/../../common/ui.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
foreach (['logged_account_id','logged_staff_id','logged_staff_name'] as $key) unset($_SESSION[$key]);
session_regenerate_id(true);
redirect_to('login.php');
}
ui_start('Log out','Confirm when you are ready to end this session.');
?><form method="post"><?= csrf_field() ?><?php ui_actions('Log out','/adminSide/posBackend/posTable.php','Stay signed in'); ?></form><?php ui_end(); ?>
