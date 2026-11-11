<?php require_once __DIR__ . '/../../common/bootstrap.php'; ?>
<?php
require_once __DIR__ . '/../../common/database.php';
if (!isset($_SESSION['logged_account_id'])) redirect_to('/adminSide/StaffLogin/login.php');
$verifiedStaff = current_staff($link);
if (!$verifiedStaff) {
    unset($_SESSION['logged_account_id']);
    redirect_to('/adminSide/StaffLogin/login.php');
}
$route = $_SERVER['SCRIPT_NAME'] ?? '';
if (preg_match('~/(accountCrud|customerCrud|staffCrud|tableCrud|menuCrud|billsCrud|report)/|/panel/(account|customer|staff|table|menu|bill|sales|statistics|profiles)-panel\\.php$~', $route)) {
    require_staff_role($link, ['Manager']);
}
