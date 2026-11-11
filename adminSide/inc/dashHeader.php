<?php
require_once __DIR__ . '/../../common/ui.php';
$staffUi = $verifiedStaff ?? current_staff($link);
$managerUi = strcasecmp($staffUi['role'] ?? '', 'Manager') === 0;
$navItems = [
    ['Point of Sale','/adminSide/posBackend/posTable.php',false],
    ['Bills','/adminSide/panel/bill-panel.php',true],
    ['Table','/adminSide/panel/table-panel.php',true],
    ['Menu','/adminSide/panel/menu-panel.php',true],
    ['Reservations','/adminSide/panel/reservation-panel.php',false],
    ['Members','/adminSide/panel/customer-panel.php',true],
    ['Staff','/adminSide/panel/staff-panel.php',true],
    ['View All Accounts','/adminSide/panel/account-panel.php',true],
    ['Kitchen','/adminSide/panel/kitchen-panel.php',false],
];
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Staff dashboard | Johnny's</title><?= ui_assets() ?><?php if(isset($staffRefresh)): ?><meta http-equiv="refresh" content="<?= (int)$staffRefresh ?>"><?php endif; ?><script src="/adminSide/js/scripts.js?v=<?= filemtime(__DIR__.'/../js/scripts.js') ?>" defer></script></head>
<body class="ui-body staff-body sb-nav-fixed"><a class="skip-link" href="#page-content">Skip to content</a>
<header class="staff-topbar"><div class="staff-brand"><button type="button" class="btn btn-small" id="sidebarToggle" aria-controls="layoutSidenav_nav" aria-expanded="false">Menu</button><a class="wordmark" href="/adminSide/posBackend/posTable.php">JOHNNY'S<span>Staff workspace</span></a></div><span class="topbar-label"><?= h($staffUi['role'] ?? 'Staff') ?></span></header>
<aside id="layoutSidenav_nav" class="staff-sidebar"><nav aria-label="Staff navigation"><p class="nav-section-title">Restaurant operations</p>
<?php foreach ($navItems as [$label,$href,$managerOnly]): if ($managerOnly && !$managerUi) continue; ?>
<a class="nav-link" href="<?= h($href) ?>" <?= ($_SERVER['SCRIPT_NAME'] ?? '') === $href ? 'aria-current="page"' : '' ?>><?= h($label) ?></a>
<?php endforeach; ?>
<?php if ($managerUi): ?><p class="nav-section-title">Report &amp; analytics</p>
<?php foreach ([['Items Sales','sales'],['Revenue Statistics','statistics'],['Member Profiles','profiles']] as [$label,$panel]): $href='/adminSide/panel/'.$panel.'-panel.php'; ?>
<a class="nav-link" href="<?= h($href) ?>" <?= ($_SERVER['SCRIPT_NAME'] ?? '') === $href ? 'aria-current="page"' : '' ?>><?= h($label) ?></a>
<?php endforeach; endif; ?><p class="nav-section-title">Account</p><a class="nav-link" href="/adminSide/StaffLogin/logout.php">Log out</a></nav>
<div class="staff-user">Logged in as<strong><?= h($staffUi['staff_name'] ?? '') ?></strong><span>Staff ID: <?= h($staffUi['staff_id'] ?? '') ?> &middot; <?= h($staffUi['role'] ?? '') ?></span></div></aside>
<button type="button" class="staff-backdrop" aria-label="Close navigation"></button><div id="layoutSidenav_content" class="staff-main"><main id="page-content" class="staff-content">
