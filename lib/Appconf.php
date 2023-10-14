<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
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
 */

/**
 * This class manages access to the configuration settings for all apps.
 * In the usage example, both forms of each are equivalent, with the
 * former being the recommended style for brevity.
 *
 * Usage:
 *
 *     <?php
 *     
 *     // get all settings for the myapp app
 *     info (Appconf::myapp ());
 *     info (Appconf::get ('myapp'));
 *     
 *     // get the 'Admin' section of myapp's settings
 *     info (Appconf::myapp ('Admin'));
 *     info (Appconf::get ('myapp', 'Admin'));
 *     
 *     // get the version of myapp
 *     info (Appconf::myapp ('Admin', 'version'));
 *     info (Appconf::get ('myapp', 'Admin', 'version'));
 *     
 *     // update the version setting
 *     Appconf::myapp ('Admin', 'version', '1.2.0');
 *     Appconf::set ('myapp', 'Admin', 'version', '1.2.0');
 *
 *     // merge new settings for an app
 *     $merged = Appconf::merge ('user', array (
 *         'User' => array (
 *             'logout_redirect' => '/'
 *             // etc
 *         )
 *     ));
 *     
 *     ?>
 */
class Appconf {
	/**
	 * The settings array for all apps.
	 */
	protected static $appconf = array ();

	/**
	 * Get the configuration settings for an app.
	 * Returns an array of all settings.
	 */
	private static function _conf ($app) {
		if (! isset (self::$appconf[$app])) {
			try {
				// First load the default configuration
				self::$appconf[$app] = file_exists ('apps/' . $app . '/conf/config.php')
					? parse_ini_file ('apps/' . $app . '/conf/config.php', true)
					: array ();
			} catch (Exception $e) {
				// Catch and set to empty
				error_log ($e->getMessage ());
				self::$appconf[$app] = array ();
			}
			
			try {
				// Now check for custom configuration for default environment
				self::$appconf[$app] = file_exists ('conf/app.' . $app . '.config.php')
					? array_replace_recursive (
						self::$appconf[$app],
						parse_ini_file ('conf/app.' . $app . '.config.php', true)
					  )
					: self::$appconf[$app];
			} catch (Exception $e) {
				// Do nothing because self::$appconf[$app] is already set
				error_log ($e->getMessage ());
			}
			
			if (defined ('ELEFANT_ENV') && ELEFANT_ENV !== 'config') {
				// Check for custom configuration for alternate environemnt
				try {
					self::$appconf[$app] = file_exists ('conf/app.' . $app . '.' . ELEFANT_ENV . '.php')
						? array_replace_recursive (
							self::$appconf[$app],
							parse_ini_file ('conf/app.' . $app . '.' . ELEFANT_ENV . '.php', true)
						  )
						: self::$appconf[$app];
				} catch (Exception $e) {
					// Do nothing
					error_log ($e->getMessage ());
				}
			}
		}
		return self::$appconf[$app];
	}

	/**
	 * Get/set a configuration value for a given app. Use the app
	 * name as the method name, using the form:
	 *
	 *     Appconf::appname ($section = null, $setting = null, $new_value = null);
	 */
	public static function __callStatic ($app, $args) {
		array_unshift ($args, $app);
		if (count ($args) === 4 && $args[3] !== null) {
			return call_user_func_array ('Appconf::set', $args);
		}
		return call_user_func_array ('Appconf::get', $args);
	}

	/**
	 * Get a configuration value for an app. Can retrieve
	 * the entire app's settings, a section of the settings, or
	 * an individual value.
	 *
	 * - $app is the app name
	 * - $section is the section of the settings
	 * - $setting is the individiual setting name
	 *
	 * If no $section is specified, the entire configuration for
	 * the app will be returned as an array.
	 *
	 * If no $setting is specified, the section will be returned
	 * as an array.
	 */
	public static function get ($app, $section = null, $setting = null) {
		$conf = self::_conf ($app);
		if ($setting && $section) {
			return (isset ($conf[$section]) && isset ($conf[$section][$setting])) ? $conf[$section][$setting] : null;
		}
		if ($section) {
			return isset ($conf[$section]) ? $conf[$section] : null;
		}
		return $conf;
	}

	/**
	 * Set a configuration value for an app. Note that this does
	 * not save the changes permanently, only for the current
	 * request.
	 *
	 * - $app is the app name
	 * - $section is the section of the settings
	 * - $setting is the individiual setting name
	 * - $new_value is the value to update the setting with
	 */
	public static function set ($app, $section, $setting, $new_value) {
		$conf = self::_conf ($app);
		$conf[$section][$setting] = $new_value;
		self::$appconf[$app] = $conf;
		return $new_value;
	}

	/**
	 * Get an updated configuration for an app based on its current
	 * settings and an array of new values. This new configuration
	 * can be saved to `conf/app.APP_NAME.ELEFANT_ENV.php` which will
	 * override the values in the original `apps/APP_NAME/conf/config.php`
	 * file. Note that the `[Admin]` section is omitted, since this
	 * should not be saved to custom configuration files.
	 *
	 * - $app is the app name
	 * - $new_values is the array of custom settings
	 *
	 * Note: Use `Ini::write()` for writing the output to disk.
	 */
	public static function merge ($app, $new_values) {
		$conf = self::_conf ($app);
		$merged = array_replace_recursive ($conf, $new_values);
		unset ($merged['Admin']);
		return $merged;
	}
	
	/**
	 * Fetch all published hook handlers from a specific config file in
	 * across all apps. This may be a specific file, or a specific
	 * option list within the file.
	 *
	 * Usage:
	 *
	 *     $payment_options = Appconf::options ('payments');
	 *     // array ('stripe/payment' => 'Stripe Payments')
	 *     
	 *     $commands = Appconf::options ('cli', 'commands');
	 *     // array ('blog/publish-queue' => 'Publish scheduled...', ...etc...)
	 */
	public static function options ($basename = 'hooks', $hook = false) {
		$files = glob ('apps/*/conf/' . $basename . '.php');
		$options = array ();

		foreach ($files as $file) {
			$hooks = parse_ini_file ($file);
			if ($hook !== false) {
				if (isset ($hooks[$hook]) && is_array ($hooks[$hook])) {
					foreach ($hooks[$hook] as $handler => $name) {
						$options[$handler] = $name;
					}
				}
			} else {
				$options = array_merge ($options, $hooks);
			}
		}

		return $options;
	}
}
