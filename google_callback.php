<?php
include 'components/connect.php';
require_once 'components/google_auth.php';

session_start();

$callbackUri = env('GOOGLE_REDIRECT_URI', 'http://localhost:8000/user_login.php');
// In case redirected to this dedicated script
$currentUri = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000') . '/google_callback.php';

// Try standard redirect URI first, fallback to self
$result = process_google_callback($conn, $callbackUri);
if ($result === null || (!empty($result['error']) && strpos($result['error'], 'redirect_uri_mismatch') !== false)) {
    $result = process_google_callback($conn, $currentUri);
}

if (!empty($result['success'])) {
    header('location:home.php');
    exit;
} else {
    $err = $result['error'] ?? 'Google authentication failed.';
    $_SESSION['auth_flash_error'] = $err;
    header('location:user_login.php');
    exit;
}

