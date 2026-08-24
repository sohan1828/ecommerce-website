<?php
// Simple CSRF protection helpers.
// Requires session_start() to have been called before these are used.

// Returns the current CSRF token, generating one if it doesn't exist yet.
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Echoes a hidden input field carrying the current CSRF token. Use inside <form>...</form>.
function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

// Verifies a submitted CSRF token against the one stored in the session.
function csrf_verify() {
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

// Verifies the CSRF token for the current POST request, or stops the request with a 403.
// Call this once, right after session_start(), before any $_POST[...] handling.
function csrf_verify_or_die() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify()) {
        http_response_code(403);
        die('Sorry, your session has expired or this request could not be verified. Please go back and try again.');
    }
}
?>
