<?php require_once __DIR__ . '/../../common/bootstrap.php'; ?>
<?php
require_once __DIR__ . '/../posBackend/checkIfLoggedIn.php';
require_once __DIR__ . '/../../common/operations.php';
post_only();
$_SESSION['reservation_owner'] ??= bin2hex(random_bytes(32));
$id=reserve($_POST,$_SESSION['account_id'] ?? null,$_SESSION['reservation_owner']);
redirect_to('reservationReceipt.php?reservation_id=' . $id);
