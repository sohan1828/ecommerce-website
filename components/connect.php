<?php
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "ecom"; // Ensure this matches the database name

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

catch(PDOException $e) {
    // Don't leak DB details to the browser. Log internally and stop.
    error_log('DB connection failed: ' . $e->getMessage());
    die('Sorry, something went wrong. Please try again later.');
}

require_once __DIR__ . '/product_display_helpers.php';
require_once __DIR__ . '/csrf.php';

// Harden session cookie behaviour. Must run before session_start() is called
// by the including page, so it needs to sit here since connect.php is
// included first everywhere.
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