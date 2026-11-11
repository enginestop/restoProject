<?php require_once __DIR__ . '/../../common/bootstrap.php'; ?>
<?php
require_once __DIR__ . '/../../common/accounts.php';
$email_err=$member_name_err=$password_err=$phone_number_err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $_POST['points']='0';
    create_identity($_POST,'member');
    redirect_to('register_process.php');
}
?>
<?php
require_once __DIR__ . '/../../common/ui.php';
ui_start('Create your account',"Join Johnny's and keep your reservations and points together.");
?><form method="post"><div class="form-stack"><?php ui_field('email','Email',['type'=>'email','autocomplete'=>'email','error'=>$email_err]); ui_field('member_name','Member name',['autocomplete'=>'name','error'=>$member_name_err,'maxlength'=>255]); ui_field('password','Password',['type'=>'password','autocomplete'=>'new-password','error'=>$password_err,'minlength'=>6,'maxlength'=>72,'hint'=>'Use at least 6 characters.']); ui_field('phone_number','Phone number',['type'=>'tel','autocomplete'=>'tel','error'=>$phone_number_err,'maxlength'=>30]); ?></div><?php ui_actions('Register','/','Home'); ?></form><p class="auth-footnote">Already have an account? <a href="login.php">Proceed to Login</a></p><?php ui_end(); ?>
