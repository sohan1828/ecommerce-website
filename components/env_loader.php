<?php
/**
 * Lightweight .env Loader for Gizmo E-Commerce
 * Automatically parses the .env file in the project root into environment variables.
 */

if (!function_exists('load_env_file')) {
    function load_env_file($path = null) {
        if ($path === null) {
            $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        }

        if (!file_exists($path) || !is_readable($path)) {
            return false;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comment lines and lines without '='
            if (empty($line) || str_starts_with($line, '#') || strpos($line, '=') === false) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Strip enclosing quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            // Set environment variable if not already set by system
            if (getenv($name) === false) {
                putenv("{$name}={$value}");
            }
            if (!isset($_ENV[$name])) {
                $_ENV[$name] = $value;
            }
            if (!isset($_SERVER[$name])) {
                $_SERVER[$name] = $value;
            }
        }

        return true;
    }
}

if (!function_exists('env')) {
    /**
     * Get an environment variable with optional default value
     */
    function env($key, $default = null) {
        $val = getenv($key);
        if ($val === false) {
            $val = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        }

        // Cast boolean and null strings
        if ($val === 'true' || $val === '(true)') return true;
        if ($val === 'false' || $val === '(false)') return false;
        if ($val === 'null' || $val === '(null)') return null;
        if ($val === 'empty' || $val === '(empty)') return '';

        return $val;
    }
}

// Auto-load .env when this file is required
load_env_file();
