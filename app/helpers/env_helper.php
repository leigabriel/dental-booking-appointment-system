<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

/**
 * Load environment variables from .env file
 * This helper automatically loads on every request
 */
if (!function_exists('load_env')) {
    function load_env($file = null)
    {
        if ($file === null) {
            $file = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . '.env';
        }

        if (!file_exists($file)) {
            return false;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $value = trim($value, '"\'');

                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }

        return true;
    }
}

load_env();