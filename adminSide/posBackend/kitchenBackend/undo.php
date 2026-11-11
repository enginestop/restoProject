<?php
require_once __DIR__ . '/../checkIfLoggedIn.php';
post_only();
$id=$_SESSION['last_kitchen_done'] ?? null;
if ($id) db('UPDATE Kitchen SET time_ended=NULL WHERE kitchen_id=?',[$id]);
unset($_SESSION['last_kitchen_done']);
redirect_to('../../panel/kitchen-panel.php');
