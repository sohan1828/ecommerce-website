<?php
/**
 * eSewa ePay 2.0 Integration Helper for Gizmo Store
 * Supports both UAT / Sandbox and Production environments.
 */

require_once __DIR__ . '/env_loader.php';

if (!function_exists('get_esewa_config')) {
    function get_esewa_config() {
        $env = strtolower(env('ESEWA_ENV', 'sandbox'));
        $isSandbox = ($env !== 'production');

        $baseUrl = $isSandbox 
            ? env('ESEWA_SANDBOX_URL', 'https://rc-epay.esewa.com.np')
            : env('ESEWA_PRODUCTION_URL', 'https://epay.esewa.com.np');

        $productCode = env('ESEWA_PRODUCT_CODE', 'EPAYTEST');
        $secretKey   = env('ESEWA_SECRET_KEY', '8gBm/:&EnhH.1/q');

        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $siteUrl = $protocol . $host;

        $successUrl = env('ESEWA_SUCCESS_URL', $siteUrl . '/esewa_success.php');
        $failureUrl = env('ESEWA_FAILURE_URL', $siteUrl . '/esewa_failure.php');

        return [
            'env'          => $env,
            'is_sandbox'   => $isSandbox,
            'endpoint'     => rtrim($baseUrl, '/') . '/api/epay/main/v2/form',
            'status_url'   => rtrim($baseUrl, '/') . '/api/epay/transaction/status/',
            'product_code' => $productCode,
            'secret_key'   => $secretKey,
            'success_url'  => $successUrl,
            'failure_url'  => $failureUrl,
        ];
    }
}

if (!function_exists('generate_esewa_signature')) {
    /**
     * Generates HMAC-SHA256 signature for eSewa ePay 2.0
     *
     * @param string|numeric $total_amount
     * @param string $transaction_uuid
     * @param string $product_code
     * @param string $secret_key
     * @return string Base64 encoded hash
     */
    function generate_esewa_signature($total_amount, $transaction_uuid, $product_code, $secret_key) {
        $message = "total_amount={$total_amount},transaction_uuid={$transaction_uuid},product_code={$product_code}";
        return base64_encode(hash_hmac('sha256', $message, $secret_key, true));
    }
}

if (!function_exists('verify_esewa_signature')) {
    /**
     * Verifies the signature received from eSewa in success callback
     *
     * @param array $payload Decoded response array from eSewa
     * @param string $secret_key
     * @return bool
     */
    function verify_esewa_signature($payload, $secret_key) {
        if (empty($payload['signature']) || empty($payload['signed_field_names'])) {
            return false;
        }

        $fields = explode(',', $payload['signed_field_names']);
        $parts = [];
        foreach ($fields as $field) {
            $field = trim($field);
            if (isset($payload[$field])) {
                $parts[] = "{$field}={$payload[$field]}";
            }
        }
        $dataString = implode(',', $parts);
        $expectedSignature = base64_encode(hash_hmac('sha256', $dataString, $secret_key, true));

        return hash_equals($expectedSignature, $payload['signature']);
    }
}

