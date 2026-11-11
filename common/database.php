<?php
require_once __DIR__ . '/bootstrap.php';
$configFile = getenv('RESTAURANT_CONFIG') ?: '/etc/restaurant-project/database.php';
if (!is_readable($configFile)) problem('Database configuration is not available.', 503);
$dbConfig = require $configFile;
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$link = new mysqli($dbConfig['host'], $dbConfig['user'], $dbConfig['password'], $dbConfig['database']);
$link->set_charset('utf8mb4');
$link->query("SET SESSION time_zone = '+08:00'");
$link->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ONLY_FULL_GROUP_BY,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
function db(string $sql, array $params = []): mysqli_result|bool {
    global $link;
    return $link->execute_query($sql, $params);
}
function one(string $sql, array $params = []): ?array { return db($sql, $params)->fetch_assoc(); }
function transaction(callable $work): mixed {
    global $link;
    $link->begin_transaction();
    try { $result = $work(); $link->commit(); return $result; }
    catch (Throwable $e) { $link->rollback(); throw $e; }
}
