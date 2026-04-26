<?php
/**
 * Security helper functions for input validation, output escaping, CSRF,
 * password handling, and application logging.
 */

function sanitize_input($input) {
    return trim((string) $input);
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_number($value, $min = 0) {
    return is_numeric($value) && floatval($value) >= $min;
}

function validate_string_length($value, $min = 1, $max = 255) {
    $len = strlen(trim((string) $value));
    return $len >= $min && $len <= $max;
}

function get_validated_int($key, $default = 0, $min = 0, $max = PHP_INT_MAX) {
    $value = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : $default;
    return ($value >= $min && $value <= $max) ? $value : $default;
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function get_csrf_token() {
    return $_SESSION['csrf_token'] ?? '';
}

function verify_csrf_token() {
    $token = $_POST['csrf_token'] ?? '';
    $valid = !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);

    if (!$valid && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        error_log("CSRF token validation failed for " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
    }

    return $valid;
}

function csrf_token_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(generate_csrf_token()) . '">';
}

function require_admin() {
    if (!isset($_SESSION['admin'])) {
        header("Location: index.php");
        exit();
    }
}

function require_admin_or_customer() {
    if (!isset($_SESSION['admin']) && !isset($_SESSION['customer'])) {
        header("Location: customer-login.php");
        exit();
    }
}

function get_flash_message($entity) {
    $allowed = ['added', 'updated', 'deleted'];
    $msg = $_GET['msg'] ?? '';
    return in_array($msg, $allowed, true) ? ucfirst($entity) . ' ' . $msg . ' successfully.' : '';
}

function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function log_event($level, $message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $user = $_SESSION['admin'] ?? 'anonymous';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $log_entry = "[$timestamp] [$level] User: $user | IP: $ip | $message";
    if (!empty($context)) {
        $log_entry .= " | Context: " . json_encode($context);
    }

    error_log($log_entry);
}

function handle_error($message, $log_message = '', $level = 'ERROR') {
    log_event($level, $log_message ?: $message);
    return $message;
}
?>
