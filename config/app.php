<?php
// ─── App Config ───────────────────────────────────────────────────────────
define('APP_NAME',    'USeP Vehicle Reservation System');
define('APP_VERSION', '1.0.0');

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$baseUrl .= str_replace(basename($scriptName), '', $scriptName);

define('BASE_URL',    $baseUrl);
define('TIMEZONE',    'Asia/Manila');
define('SESSION_NAME','usep_vrs_session');

date_default_timezone_set(TIMEZONE);

// ─── Path Helpers ─────────────────────────────────────────────────────────
define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('VIEW_PATH',   APP_PATH  . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('LOG_PATH',    ROOT_PATH . '/storage/logs/app.log');

// ─── Security Headers ─────────────────────────────────────────────────────
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');