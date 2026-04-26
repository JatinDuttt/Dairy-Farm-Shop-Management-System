<?php
/**
 * Database and application configuration.
 * Values can be overridden with environment variables for deployment.
 */

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'dfsms';
$app_env = getenv('APP_ENV') ?: 'development';

define('DB_HOST', $db_host);
define('DB_USER', $db_user);
define('DB_PASS', $db_pass);
define('DB_NAME', $db_name);
define('APP_ENV', $app_env);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}
session_start();

$logs_dir = __DIR__ . '/../logs';
if (!is_dir($logs_dir)) {
    mkdir($logs_dir, 0755, true);
}

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', $logs_dir . '/php_errors.log');

$con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$con) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die(APP_ENV === 'development' ? "Database connection failed." : "Database connection error. Please contact administrator.");
}

mysqli_set_charset($con, "utf8mb4");

if (!isset($_SESSION['_init'])) {
    session_regenerate_id(true);
    $_SESSION['_init'] = true;
}

require_once __DIR__ . '/security.php';
?>
