<?php require_once __DIR__ . '/../../common/bootstrap.php'; ?>
<?php
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';
require_once "../config.php";

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $menu_id = h($_GET['id']);
} else {
    header("Location: ../panel/menu-panel.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf_token();
    require_manager_password($link);
    header("Location: ../menuCrud/updateItem.php?id=" . urlencode($menu_id));
    exit;
}
?>

<?php include __DIR__ . '/../inc/dashHeader.php'; ?><div class="form-panel"><?php ui_heading('Confirm menu changes','Verify Manager credentials before editing this item.','Administration'); ?><section class="surface"><p class="info-note">Item ID: <strong><?= h($menu_id) ?></strong></p><form method="post"><?= csrf_field() ?><div class="form-grid"><?php ui_field('admin_id','Manager account ID',['type'=>'number','min'=>1,'autocomplete'=>'username']); ui_field('password','Manager password',['type'=>'password','autocomplete'=>'current-password']); ?></div><?php ui_actions('Continue','../panel/menu-panel.php'); ?></form></section></div><?php include __DIR__ . '/../inc/dashFooter.php'; ?>
