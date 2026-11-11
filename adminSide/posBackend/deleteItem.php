<?php require_once __DIR__ . '/../../common/bootstrap.php'; ?>
<?php
require_once __DIR__ . '/checkIfLoggedIn.php';
require_once __DIR__ . '/../../common/operations.php';
post_only();
$bill = number_id($_POST['bill_id'] ?? '');
$table = number_id($_POST['table_id'] ?? '');
remove_item($bill, $table, number_id($_POST['bill_item_id'] ?? ''));
redirect_to("orderItem.php?bill_id=$bill&table_id=$table");
