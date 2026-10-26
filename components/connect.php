<?php
require_once __DIR__ . '/env_loader.php';

$servername = env('DB_HOST', 'localhost');
$port       = env('DB_PORT', '3306');
$username   = env('DB_USER', 'root');
$password   = env('DB_PASS', 'admin');
$dbname     = env('DB_NAME', 'ecom');

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Fallback to empty password if default root password fails (e.g. standard XAMPP)
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e2) {
        error_log('DB connection failed: ' . $e2->getMessage());
        die('Sorry, something went wrong. Please try again later.');
    }
}

require_once __DIR__ . '/product_display_helpers.php';
require_once __DIR__ . '/csrf.php';


if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
}
?>