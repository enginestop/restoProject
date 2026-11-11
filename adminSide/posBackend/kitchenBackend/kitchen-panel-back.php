<?php
require_once __DIR__ . '/../checkIfLoggedIn.php';
post_only();
$id=number_id($_POST['kitchen_id'] ?? '');
if (($_POST['action'] ?? '')!=='set_time_ended') problem('Invalid action.');
if (!one('SELECT kitchen_id FROM Kitchen WHERE kitchen_id=?',[$id])) problem('Kitchen ticket not found.',404);
db('UPDATE Kitchen SET time_ended=NOW() WHERE kitchen_id=? AND time_ended IS NULL',[$id]);
if ($link->affected_rows) $_SESSION['last_kitchen_done']=$id;
redirect_to('../../panel/kitchen-panel.php');
