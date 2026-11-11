<?php require_once __DIR__ . '/../../common/bootstrap.php'; ?>
<?php
require_once __DIR__ . '/checkIfLoggedIn.php';
require_once __DIR__ . '/../../common/operations.php';
post_only();
$table = number_id($_POST['table_id'] ?? '', 'table');
$bill = open_bill($table);
redirect_to("orderItem.php?bill_id=$bill&table_id=$table");
