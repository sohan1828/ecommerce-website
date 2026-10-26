<?php
/**
 * SMTP Mail Helper for Gizmo E-Commerce
 * Reads SMTP credentials directly from .env and sends transactional emails.
 */

require_once __DIR__ . '/env_loader.php';

if (!function_exists('send_mail')) {
    /**
     * Send an email using SMTP credentials from .env
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject line
     * @param string $html_body HTML formatted message body
     * @param string $alt_body Plain text fallback message
     * @return array ['success' => bool, 'message' => string]
     */
    function send_mail($to, $subject, $html_body, $alt_body = '') {
        $host       = env('SMTP_HOST', 'smtp.gmail.com');
        $port       = (int) env('SMTP_PORT', 587);
        $user       = env('SMTP_USER', '');
        $pass       = env('SMTP_PASS', '');
        $encryption = strtolower(env('SMTP_ENCRYPTION', 'tls'));
        $fromEmail  = env('MAIL_FROM_ADDRESS', $user ?: 'noreply@gizmo.com');
        $fromName   = env('MAIL_FROM_NAME', 'Gizmo Store');

        // Basic validation
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid recipient email address.'];
        }

        // If credentials are placeholders or not provided, log and return graceful simulation
        if (empty($user) || empty($pass) || $pass === 'your_gmail_app_password_here' || $pass === 'your_app_specific_password') {
            error_log("[Gizmo Mailer] Simulated email to {$to} - Subject: {$subject}");
            return [
                'success' => true,
                'simulated' => true,
                'message' => 'Email simulated (Configure real SMTP_USER and SMTP_PASS in .env to send real emails).'
            ];
        }

        $timeout = 15;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $transport = ($encryption === 'ssl') ? "ssl://{$host}" : "tcp://{$host}";
        $socket = @stream_socket_client("{$transport}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);

        if (!$socket) {
            error_log("[Gizmo Mailer] SMTP connection failed: {$errstr} ({$errno})");
            return ['success' => false, 'message' => "SMTP connection failed: {$errstr}"];
        }

        stream_set_timeout($socket, $timeout);

        $read = function() use ($socket) {
            $data = '';
            while ($str = fgets($socket, 515)) {
                $data .= $str;
                if (substr($str, 3, 1) === ' ') break;
            }
            return $data;
        };

        $write = function($cmd) use ($socket) {
            fputs($socket, $cmd . "\r\n");
        };

        $expect = function($expectedCode) use ($read) {
            $response = $read();
            $code = substr($response, 0, 3);
            return ($code == $expectedCode);
        };

        try {
            if (!$expect(220)) throw new Exception("Server not ready");

            $write("EHLO " . gethostname());
            if (!$expect(250)) throw new Exception("EHLO command failed");

            // Handle STARTTLS
            if ($encryption === 'tls') {
                $write("STARTTLS");
                if (!$expect(220)) throw new Exception("STARTTLS failed");

                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new Exception("TLS negotiation failed");
                }

                $write("EHLO " . gethostname());
                if (!$expect(250)) throw new Exception("EHLO after STARTTLS failed");
            }

            // Authentication
            $write("AUTH LOGIN");
            if (!$expect(334)) throw new Exception("AUTH LOGIN failed");

            $write(base64_encode($user));
            if (!$expect(334)) throw new Exception("Username rejected");

            $write(base64_encode($pass));
            if (!$expect(235)) throw new Exception("Password rejected - check SMTP_USER and SMTP_PASS in .env");

            // Mail transaction
            $write("MAIL FROM: <{$fromEmail}>");
            if (!$expect(250)) throw new Exception("MAIL FROM rejected");

            $write("RCPT TO: <{$to}>");
            if (!$expect(250)) throw new Exception("RCPT TO rejected");

            $write("DATA");
            if (!$expect(354)) throw new Exception("DATA rejected");

            // Build headers and content
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>\r\n";
            $headers .= "To: <{$to}>\r\n";
            $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
            $headers .= "Date: " . date('r') . "\r\n";

            $write($headers . "\r\n" . $html_body . "\r\n.");
            if (!$expect(250)) throw new Exception("Message transmission rejected");

            $write("QUIT");
            fclose($socket);

            return ['success' => true, 'message' => 'Email sent successfully via SMTP.'];
        } catch (Exception $e) {
            @fclose($socket);
            error_log("[Gizmo Mailer] SMTP Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
