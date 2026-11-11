<?php require_once __DIR__ . '/../../common/bootstrap.php'; ?>
<?php
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';
require_once "../config.php";
require_staff_role($link, ['Manager']);

$item_id = $item_name = $item_type = $item_category = $item_price = $item_description = "";

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $item_id = trim($_GET['id']);

    $stmt = mysqli_prepare($link, "SELECT * FROM Menu WHERE item_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $param_item_id);
    $param_item_id = $item_id;

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $item_name = $row['item_name'];
            $item_type = $row['item_type'];
            $item_category = $row['item_category'];
            $item_price = $row['item_price'];
            $item_description = $row['item_description'];
        } else {
            problem('Item not found.', 404);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error retrieving item details.";
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf_token();
    require_once __DIR__ . '/../../common/accounts.php';
    [$item_id,$item_name,$item_type,$item_category,$item_price,$item_description]=menu_input($_POST);
    $stmt_update = mysqli_prepare($link, "UPDATE Menu SET item_name=?, item_type=?, item_category=?, item_price=?, item_description=? WHERE item_id=?");
    mysqli_stmt_bind_param($stmt_update, "sssdss", $item_name, $item_type, $item_category, $item_price, $item_description, $item_id);

    if (mysqli_stmt_execute($stmt_update)) {
        header("Location: ../panel/menu-panel.php");
        exit();
    } else {
        echo "Error updating item.";
    }
    mysqli_stmt_close($stmt_update);
}
?>

<?php include __DIR__ . '/../inc/dashHeader.php'; ?>
<div class="form-panel"><?php ui_heading('Update menu item','Changes apply to new orders. Existing order prices remain saved.','Administration'); ?><section class="surface"><form method="post"><?= csrf_field() ?><input type="hidden" name="item_id" value="<?= h($item_id) ?>"><p class="info-note">Item ID: <strong><?= h($item_id) ?></strong></p><div class="form-grid"><?php
ui_field('item_name','Item name',['value'=>$item_name,'maxlength'=>255]);
ui_field('item_type','Item type',['value'=>$item_type,'maxlength'=>255]);
ui_field('item_category','Category',['value'=>$item_category,'choices'=>['Main Dishes','Side Snacks','Drinks']]);
ui_field('item_price','Price (RM)',['value'=>$item_price,'type'=>'number','min'=>'0.01','step'=>'0.01']);
ui_field('item_description','Description',['value'=>$item_description,'type'=>'textarea','required'=>false,'maxlength'=>255,'full'=>true]);
?></div><?php ui_actions('Update','../panel/menu-panel.php'); ?></form></section></div><?php include __DIR__ . '/../inc/dashFooter.php'; ?>
