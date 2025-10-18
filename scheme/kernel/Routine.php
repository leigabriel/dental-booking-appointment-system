<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @copyright Copyright 2020 (https://ronmarasigan.github.io)
 * @since Version 1
 * @link https://lavalust.pinoywap.org
 * @license https://opensource.org/licenses/MIT MIT License
 */

if ( ! function_exists('load_class'))
{
	/**
	 * Class Loader to load all classes
	 * @param  string $class
	 * @param  string $directory Class directory
	 * @param  array $params    Class parameters if present
	 * @return object
	 */
	function load_class(string $class, string $directory = '', $params = null, $object_name = null)
{
    $LAVA = Registry::instance();
    $object_name = $object_name ?? strtolower($class);

    // Return if already loaded
    if ($existing = $LAVA->get_object($object_name)) {
        return $existing;
    }

    // Search in both APP_DIR and SYSTEM_DIR
    foreach ([APP_DIR, SYSTEM_DIR] as $base_path) {
        $dir_path = rtrim($base_path . $directory, '/\\') . DIRECTORY_SEPARATOR;

        if (!is_dir($dir_path)) {
            continue;
        }

        foreach (scandir($dir_path) as $file) {
            // Case-insensitive file match
            if (strcasecmp($file, $class . '.php') !== 0) {
                continue;
            }

            require_once $dir_path . $file;

            // Find the actual class name in a case-insensitive way
            $match = null;
            foreach (get_declared_classes() as $declared_class) {
                if (strcasecmp($declared_class, $class) === 0) {
                    $match = $declared_class;
                    break;
                }
            }

            if ($match === null) {
                throw new RuntimeException("Class '{$class}' not found in file '{$file}'.");
            }

            // Register loaded class
            loaded_class($class, $object_name);

            // Instantiate the class
            $instance = isset($params) ? new $match($params) : new $match();
            $LAVA->store_object($object_name, $instance);

            return $LAVA->get_object($object_name);
        }
    }

    throw new RuntimeException("Unable to locate the '{$class}' class in '{$directory}'.");
}

}

if ( ! function_exists('loaded_class'))
{
	/**
	 * Keeps track of which libraries have been loaded. This function is
	 * called by the load_class() function above
	 *
	 * @param	string
	 * @return	array
	 */
	function loaded_class($class = '', $object_name = '')
	{
		static $_is_loaded = array();

		if ($class !== '')
		{
			$_is_loaded[$object_name] = ucfirst(strtolower($class));
		}

		return $_is_loaded;
	}
}

if ( ! function_exists('show_404'))
{
	/**
	 * 404 Error Not Found
	 * @param  string $heading
	 * @param  string $message
	 * @param  string $template
	 * @return string
	 */
	function show_404($heading = '', $message = '', $template = '')
	{
		$errors = load_class('Errors', 'kernel');
		return $errors->show_404($heading, $message, $template);
	}
}

if ( ! function_exists('show_error'))
{
	/**
	 * Show error for debugging
	 * @param  string $heading
	 * @param  string $message
	 * @param  string $code
	 * @return string
	 */
	function show_error($heading = '', $message = '', $template = 'error_general', $code = 500)
	{
	  	$errors = load_class('Errors', 'kernel');
	  	return $errors->show_error($heading, $message, $template, $code);
	}
}

if ( ! function_exists('_shutdown_handler'))
{
	/**
	 * For Debugging
	 * @return string
	 */
	function _shutdown_handler()
	{
		$last_error = error_get_last();
		if (isset($last_error) &&
			($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING)))
		{
			_error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
		}
	}
}

if ( ! function_exists('_exception_handler'))
{
	/**
	 * For Debgging
	 * @param  object $e
	 * @return string
	 */
	function _exception_handler($e)
	{
		if(config_item('log_threshold') == 1 || config_item('log_threshold') == 3)
		{
			$logger = load_class('logger', 'kernel');
			$logger->log('error', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
		}
		if(strtolower(config_item('ENVIRONMENT') == 'development'))
		{
			$exception = load_class('Errors', 'kernel');
			$exception->show_exception($e);
		}
		
	}
}

if ( ! function_exists('_error_handler'))
{
	/**
	 * For Debugging
	 * @param  string $errno
	 * @param  string $errstr
	 * @param  string $errfile
	 * @param  string $errline
	 * @return string
	 */
	function _error_handler($severity, $errstr, $errfile, $errline)
	{
		// Map of PHP error levels
		$error_levels = [
			E_ERROR => "E_ERROR",
			E_WARNING => "E_WARNING",
			E_PARSE => "E_PARSE",
			E_NOTICE => "E_NOTICE",
			E_CORE_ERROR => "E_CORE_ERROR",
			E_CORE_WARNING => "E_CORE_WARNING",
			E_COMPILE_ERROR => "E_COMPILE_ERROR",
			E_COMPILE_WARNING => "E_COMPILE_WARNING",
			E_USER_ERROR => "E_USER_ERROR",
			E_USER_WARNING => "E_USER_WARNING",
			E_USER_NOTICE => "E_USER_NOTICE",
			E_STRICT => "E_STRICT",
			E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
			E_DEPRECATED => "E_DEPRECATED",
			E_USER_DEPRECATED => "E_USER_DEPRECATED",
		];

		// Convert severity number to string name
		$severity_name = $error_levels[$severity] ?? "UNKNOWN_ERROR";

		if (config_item('log_threshold') == 1 || config_item('log_threshold') == 3) {
			$logger = load_class('logger', 'kernel');
			$logger->log('error', $severity_name, $errstr, $errfile, $errline);
		}

		if (strtolower(config_item('ENVIRONMENT')) == 'development') { 
			$error = load_class('Errors', 'kernel');
			$error->show_php_error($severity_name, $errstr, $errfile, $errline);
		}
	}
}

if (!function_exists('get_config')) {
    /**
     * Returns global config array. Optionally merges new config.
     *
     * @param array|null $new_config
     * @return array
     */
    function get_config(?array $new_config = null)
    {
        static $config = null;

        if ($config === null) {
            // Load main config.php first
            $main_file = APP_DIR . 'config/config.php';

            require_once($main_file); // must define $config array

            if (!isset($config) || !is_array($config)) {
                throw new RuntimeException('config.php must define $config array');
            }
        }

        // Merge new configs if provided
        if (is_array($new_config)) {
            $config = array_merge($config, $new_config);
        }

        return $config;
    }
}

if ( ! function_exists('config_item'))
{
	/**
	 * Global Function to access config
	 *
	 * @param string $item
	 * @return mixed
	 */
	function config_item($item)
    {
        $config = get_config();
        return $config[$item] ?? null;
    }
}

if ( ! function_exists('autoload_config'))
{
	/**
	 * To access config from config config/autoload.php
	 *
	 * @return void
	 */
	function autoload_config()
	{
		static $autoload;

		if ( file_exists(APP_DIR . 'config/autoload.php') )
		{
			require_once APP_DIR . 'config/autoload.php';

			if ( isset($autoload)  OR is_array($autoload) )
			{
				foreach( $autoload as $key => $val )
				{
					$autoload[$key] = $val;
				}

				return $autoload;
			}
		} else
			show_404('404 Not Found', 'The configuration file does not exist');
	}
}

if ( ! function_exists('database_config'))
{
	/**
	 * To access config from config config/database.php
	 *
	 * @return void
	 */
	function database_config()
	{
		static $database;

		if ( file_exists(APP_DIR . 'config/database.php') )
		{
			require_once APP_DIR . 'config/database.php';

			if ( isset($database)  OR is_array($database) )
			{
				foreach( $database as $key => $val )
				{
					$database[$key] = $val;
				}

				return $database;
			}
		} else
			show_404('404 Not Found', 'The configuration file does not exist');
	}
}

if ( ! function_exists('route_config'))
{
	/**
	 * To access config from config config/routes.php
	 *
	 * @return void
	 */
	function route_config()
	{
		static $route;

		if ( file_exists(APP_DIR . 'config/routes.php') )
		{
			require_once APP_DIR . 'config/routes.php';

			if ( isset($route)  OR is_array($route) )
			{
				foreach( $route as $key => $val )
				{
					$route[$key] = $val;
				}

				return $route;
			}
		} else {
			show_404('404 Not Found', 'The configuration file does not exist');
		}
	}
}

if ( ! function_exists('html_escape'))
{
	/**
	 * Returns HTML escaped variable.
	 *
	 * @param	mixed	$var		The input string or array of strings to be escaped.
	 * @param	bool	$double_encode	$double_encode set to FALSE prevents escaping twice.
	 * @return	mixed			The escaped string or array of strings as a result.
	 */
	function html_escape($var, $double_encode = TRUE)
	{
		if (empty($var))
		{
			return $var;
		}

		if (is_array($var))
		{
			foreach (array_keys($var) as $key)
			{
				$var[$key] = html_escape($var[$key], $double_encode);
			}

			return $var;
		}

		return htmlspecialchars($var, ENT_QUOTES, config_item('charset'), $double_encode);
	}
}

if ( ! function_exists('is_php'))
{
	/**
	 * Determines if the current version of PHP is equal to or greater than the supplied value
	 *
	 * @param	string
	 * @return	bool	TRUE if the current version is $version or higher
	 */
	function is_php($version)
	{
		static $_is_php;
		$version = (string) $version;

		if ( ! isset($_is_php[$version]))
		{
			$_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
		}

		return $_is_php[$version];
	}
}

if ( ! function_exists('is_https'))
{
	/**
	 * Is HTTPS?
	 *
	 * Determines if the application is accessed via an encrypted
	 * (HTTPS) connection.
	 *
	 * @return	bool
	 */
	function is_https()
	{
		if ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
		{
			return TRUE;
		}
		elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
		{
			return TRUE;
		}
		elseif ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
		{
			return TRUE;
		}

		return FALSE;
	}
}