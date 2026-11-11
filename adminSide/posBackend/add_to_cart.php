<?php require_once __DIR__ . '/../../common/bootstrap.php'; ?>
<?php
require_once __DIR__ . '/checkIfLoggedIn.php';
post_only();
$_POST['quantity'] = '1';
require __DIR__ . '/addItem.php';
