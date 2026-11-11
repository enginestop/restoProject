<?php require_once __DIR__ . '/../../common/bootstrap.php'; ?>
<?php
require_once __DIR__ . '/../../common/database.php';
$email_err=$password_err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email=text_input($_POST['email'] ?? '', 'email');
    $password=text_input($_POST['password'] ?? '', 'password',72);
    $row=one('SELECT a.*,m.member_name FROM Accounts a JOIN Memberships m ON m.account_id=a.account_id WHERE a.email=?',[$email]);
    if ($row && password_verify($password,$row['password'])) {
        session_regenerate_id(true);
        $_SESSION['loggedin']=true; $_SESSION['email']=$row['email']; $_SESSION['account_id']=$row['account_id']; $_SESSION['member_name']=$row['member_name'];
        redirect_to('../home/home.php');
    }
    $password_err='Invalid email or password.';
}
?>
<?php
require_once __DIR__ . '/../../common/ui.php';
ui_start('Welcome back','Sign in to view your profile, points and reservations.');
?><form method="post"><div class="form-stack"><?php ui_field('email','Email',['type'=>'email','autocomplete'=>'username','error'=>$email_err]); ui_field('password','Password',['type'=>'password','autocomplete'=>'current-password','error'=>$password_err]); ?></div><?php ui_actions('Login','/','Home'); ?></form><p class="auth-footnote">Need an account? <a href="register.php">Proceed to Register</a></p><?php ui_end(); ?>
