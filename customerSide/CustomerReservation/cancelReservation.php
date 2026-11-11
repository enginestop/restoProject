<?php require_once __DIR__ . '/../../common/bootstrap.php'; ?>
<?php
require_once __DIR__ . '/../../common/operations.php';
post_only();
$id=number_id($_POST['reservation_id'] ?? '');
$row=one('SELECT * FROM Reservations WHERE reservation_id=?',[$id]);
if (!$row || !reservation_owner($row)) problem('You do not have access to this reservation.',403);
cancel_reservation($id);
redirect_to('reservePage.php');
