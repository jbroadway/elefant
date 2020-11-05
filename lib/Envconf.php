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
 * Wraps `Appconf`'s getters in a check to see whether the equivalent environment
 * variable has been set, and if so, returns that instead. For implementing
 * sensiting settings in apps that you want to inject via environment variables
 * and use the built-in system as a transparent fallback.
 *
 * The naming of the environment variables are the concatenated names of the
 * app, section, and setting. For example:
 *
 *     <?php
 *     
 *     // Returns $_ENV['MYAPP_S3_ACCESS_TOKEN'] if set.
 *     // Otherwise, returns Appconf::myapp ('S3', 'access_token')
 *     $token = Envconf::myapp ('S3', 'access_token');
 *     
 *     // Alternate syntax
 *     $token = Envconf::get ('myapp', 'S3', 'access_token');
 *
 * Note that, unlike `Appconf::get()`, this class returns individual values only.
 * For example, you can't call `Envconf::get ('myapp', 'Section')` to retrieve
 * an array of values. This corresponds to Elefant's use of INI file sections. 
 */
class Envconf {
	/**
	 * Get an individual configuration value for a given app setting. Use the app
	 * name as the method name, using the form:
	 *
	 *     Appconf::appname ($section, $setting);
	 */
	public static function __callStatic ($app, $args) {
		array_unshift ($args, $app);
		return call_user_func_array ('self::get', $args);
	}
	
	/**
	 * Get an individual configuration value for an app.
	 *
	 * - $app is the app name
	 * - $section is the section of the settings
	 * - $setting is the individiual setting name
	 */
	public static function get ($app, $section, $setting) {
		$envkey = strtoupper ($app . '_' . $section . '_' . $setting);

		$env = getenv ($envkey);
		
		if ($env !== false) {
			return $env;
		}
		
		return Appconf::get ($app, $section, $setting);
	}
}