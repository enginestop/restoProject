<?php require_once __DIR__ . '/../../common/bootstrap.php'; ?>
<?php
require_once __DIR__ . '/../../common/operations.php';
reservation_input($_GET);
redirect_to('reservePage.php?' . http_build_query(array_intersect_key($_GET,array_flip(['reservation_date','reservation_time','head_count']))));
