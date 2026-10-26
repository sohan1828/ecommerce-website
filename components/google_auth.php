<?php
/**
 * Google OAuth 2.0 Integration Helper for Gizmo Store
 * Handles Google authentication redirect and token exchange callback.
 */

require_once __DIR__ . '/env_loader.php';

if (!function_exists('get_google_auth_url')) {
    /**
     * Generate the Google OAuth authorization URL
     *
     * @param string|null $redirectUri
     * @return string|null
     */
    function get_google_auth_url($redirectUri = null) {
        $clientId = env('GOOGLE_CLIENT_ID');
        if (empty($clientId) || $clientId === 'your_google_client_id.apps.googleusercontent.com') {
            return null;
        }

        if (empty($redirectUri)) {
            $redirectUri = env('GOOGLE_REDIRECT_URI', 'http://localhost:8000/user_login.php');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Store CSRF state token in session
        $state = bin2hex(random_bytes(16));
        $_SESSION['google_oauth_state'] = $state;

        $params = [
            'client_id'             => $clientId,
            'redirect_uri'          => $redirectUri,
            'response_type'         => 'code',
            'scope'                 => 'openid email profile',
            'state'                 => $state,
            'access_type'           => 'online',
            'include_granted_scopes'=> 'true',
            'prompt'                => 'select_account'
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }
}

if (!function_exists('process_google_callback')) {
    /**
     * Process authorization code from Google OAuth redirect
     *
     * @param PDO $conn
     * @param string|null $redirectUri
     * @return array ['success' => bool, 'error' => string|null, 'user_id' => int|null]
     */
    function process_google_callback($conn, $redirectUri = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user cancelled authorization
        if (isset($_GET['error'])) {
            $err = htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8');
            return [
                'success' => false,
                'error'   => ($err === 'access_denied') ? 'Google sign-in was cancelled.' : "Google authentication error: {$err}"
            ];
        }

        if (!isset($_GET['code'])) {
            return null; // Not an OAuth callback
        }

        // Verify CSRF state token
        $returnedState = $_GET['state'] ?? '';
        $storedState = $_SESSION['google_oauth_state'] ?? '';
        unset($_SESSION['google_oauth_state']);

        if (empty($returnedState) || empty($storedState) || !hash_equals($storedState, $returnedState)) {
            return [
                'success' => false,
                'error'   => 'Google session security validation failed. Please try signing in again.'
            ];
        }

        $code = $_GET['code'];
        $clientId = env('GOOGLE_CLIENT_ID');
        $clientSecret = env('GOOGLE_CLIENT_SECRET');

        if (empty($redirectUri)) {
            $redirectUri = env('GOOGLE_REDIRECT_URI', 'http://localhost:8000/user_login.php');
        }

        // 1. Exchange authorization code for access token
        $tokenUrl = 'https://oauth2.googleapis.com/token';
        $postData = [
            'code'          => $code,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code'
        ];

        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $tokenResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || !$tokenResponse) {
            error_log("[Google OAuth] Token exchange error (HTTP {$httpCode}): {$curlErr} - Response: {$tokenResponse}");
            return [
                'success' => false,
                'error'   => 'Could not authenticate with Google. Please check your credentials or try again.'
            ];
        }

        $tokenData = json_decode($tokenResponse, true);
        $accessToken = $tokenData['access_token'] ?? '';
        if (empty($accessToken)) {
            return [
                'success' => false,
                'error'   => 'Invalid response from Google authentication service.'
            ];
        }

        // 2. Fetch authenticated user profile details
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';
        $ch = curl_init($userInfoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $userResponse = curl_exec($ch);
        $userHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($userHttpCode !== 200 || !$userResponse) {
            return [
                'success' => false,
                'error'   => 'Failed to retrieve user profile from Google.'
            ];
        }

        $userData = json_decode($userResponse, true);
        $email = filter_var($userData['email'] ?? '', FILTER_VALIDATE_EMAIL);

        if (!$email) {
            return [
                'success' => false,
                'error'   => 'Unable to retrieve a verified email address from your Google account.'
            ];
        }

        // Clean user display name (users.name is VARCHAR(20))
        $rawName = trim($userData['name'] ?? ($userData['given_name'] ?? 'Google User'));
        $name = mb_substr($rawName, 0, 20, 'UTF-8');
        if ($name === '') {
            $name = 'Google User';
        }

        try {
            // 3. Find or register user
            $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? LIMIT 1");
            $select_user->execute([$email]);
            $existingUser = $select_user->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                $userId = $existingUser['id'];
            } else {
                // Create random secure password for social user
                $randomPass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
                $insert_user = $conn->prepare("INSERT INTO `users` (name, email, password) VALUES (?, ?, ?)");
                $insert_user->execute([$name, $email, $randomPass]);
                $userId = $conn->lastInsertId();
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;

            return [
                'success' => true,
                'user_id' => $userId
            ];
        } catch (PDOException $e) {
            error_log("[Google OAuth] DB Error: " . $e->getMessage());
            return [
                'success' => false,
                'error'   => 'Database error during account sign-in. Please try again.'
            ];
        }
    }
}

