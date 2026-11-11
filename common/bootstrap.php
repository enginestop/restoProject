<?php
// Shared request boundary. Every application entry point loads this file.
date_default_timezone_set('Asia/Kuala_Lumpur');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    foreach (['session.use_strict_mode'=>'1','session.cookie_httponly'=>'1','session.cookie_samesite'=>'Lax'] as $key=>$value) {
        if (ini_get($key) !== $value) ini_set($key,$value);
    }
    session_start(['cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
}
require_once __DIR__ . '/../adminSide/posBackend/csrf.php';

class RequestProblem extends RuntimeException {
    public function __construct(string $message, public int $status = 422) { parent::__construct($message); }
}
function problem(string $message, int $status = 422): never { throw new RequestProblem($message, $status); }
function post_only(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        problem('Method not allowed.', 405);
    }
    verify_csrf_token();
}
function number_id($value, string $name = 'ID'): int {
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
    if ($id === false) problem("Invalid $name.");
    return $id;
}
function optional_id($value): ?int { return $value === null || $value === '' ? null : number_id($value); }
function text_input($value, string $name, int $max = 255, bool $required = true): string {
    if (!is_string($value)) problem("Invalid $name.");
    $value = trim($value);
    if (($required && $value === '') || mb_strlen($value) > $max || preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f]/', $value)) problem("Invalid $name.");
    return $value;
}
function cents($value): int {
    if (!is_scalar($value) || !preg_match('/^\d{1,7}(?:\.\d{1,2})?$/D', (string)$value)) problem('Enter a valid amount with at most two decimal places.');
    [$whole, $part] = array_pad(explode('.', (string)$value), 2, '');
    return (int)$whole * 100 + (int)str_pad($part, 2, '0');
}
function money(int $amount): string { return number_format($amount / 100, 2, '.', ''); }
function redirect_to(string $url): never { header('Location: ' . $url, true, 303); exit; }

set_exception_handler(function (Throwable $e): void {
    global $link;
    try { if (isset($link) && $link instanceof mysqli) $link->rollback(); } catch (Throwable $ignored) {}
    $status = 500;
    $message = 'The request could not be completed. Please try again.';
    if ($e instanceof RequestProblem) { $status = $e->status; $message = $e->getMessage(); }
    elseif ($e instanceof mysqli_sql_exception && in_array($e->getCode(), [1062, 1216, 1217, 1451, 1452, 3819, 1205, 1213], true)) {
        $status = 409;
        $message = match ($e->getCode()) {
            1062 => 'This record or reservation slot already exists.',
            1217, 1451 => 'This record is referenced by other records or transactions and cannot be deleted.',
            1205, 1213 => 'Another request is updating these records. Please retry.',
            default => 'The supplied data conflicts with an existing record or constraint.'
        };
    }
    if ($status === 500) error_log((string)$e);
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    require_once __DIR__ . '/ui.php';
    ui_start('Request result', 'Please review the message below before continuing.');
    echo '<div class="alert alert-danger" role="alert">' . h($message) . '</div><div class="form-actions"><button type="button" class="btn btn-secondary" onclick="history.back()">Go back</button><a class="btn btn-primary" href="/">Home</a></div>';
    ui_end();
});
if (PHP_SAPI !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: same-origin');
    header('Cache-Control: no-store');
    foreach ([$_GET, $_POST] as $input) foreach ($input as $value) {
        if (!is_string($value) || strlen($value) > 4096) problem('Invalid input.', 400);
    }
    // Server-rendered tokens also cover legacy forms generated inside echo statements.
    ob_start(function (string $html): string {
        if (!str_contains(strtolower($html), '<html')) return $html;
        $html = preg_replace_callback('/<form\b[^>]*>.*?<\/form>/is', function ($m) {
            if (!preg_match('/method\s*=\s*[\x22\x27]?post\b/i', $m[0]) || str_contains($m[0], 'name="csrf_token"') || str_contains($m[0], "name='csrf_token'")) return $m[0];
            return preg_replace('/(<form\b[^>]*>)/i', '$1' . csrf_field(), $m[0], 1);
        }, $html);
        if (str_contains($html, '/common/interface.css')) return $html;
        return str_ireplace('</head>', '<meta name="viewport" content="width=device-width, initial-scale=1"><link rel="icon" href="/common/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="/common/interface.css?v=' . filemtime(__DIR__ . '/interface.css') . '"></head>', $html);
    });
    csrf_token();
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') verify_csrf_token();
}
