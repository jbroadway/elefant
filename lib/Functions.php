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
 * This is a collection of utility functions that are too small to merit their
 * own class, or even their own file, so they're aggregated here. They include:
 *
 * - `info()` - Dumps a formatted data structure for quick debugging.
 * - `conf()` - Retrieves a value from the global configuration.
 * - `simple_auth()` - Provides a means of implementing custom auth schemes
 *   (includes built-in HTTP Basic support).
 * - `sql_split()` - Parses SQL data, removes comments, and splits it into
 *   individual queries.
 * - `format_filesize()` - Formats a byte value into more readable output.
 * - `detect()` - Simpel browser and browser type (e.g., mobile) detection.
 */

/**
 * Wraps a `print_r()` or `var_dump()` of the given `$value` with a set of `<pre></pre>`
 * tags around it, and echoes it.
 */
function info ($value, $full = false) {
	if (php_sapi_name () !== 'cli') {
		echo '<pre>';
		if ($full) {
			var_dump ($value);
		} else {
			print_r ($value);
		}
		echo '</pre>';
	} else {
		if ($full) {
			var_dump ($value);
		} else {
			print_r ($value);
		}
	}
}

/**
 * Get any global configuration section or individual setting value.
 * Lazy loads the configuration upon first use, and stores it privately
 * to avoid polluting the global space. Useful in templates as well.
 * Note: Uses ELEFANT_ENV, defined in the front controller, to determine
 * which configuration file to load, allowing for alternate dev, staging,
 * and production configurations in a single codebase.
 *
 * To update a value after the initial configuration has been loaded,
 * pass the new value as a third parameter, e.g.:
 *
 *     // Enable debugging
 *     conf ('General', 'debug', true);
 */
function conf ($section, $value = false, $update = null) {
	static $conf;
	if ($conf === null) {
		if (isset ($GLOBALS['conf'])) {
			$conf =& $GLOBALS['conf'];
		} elseif (defined ('ELEFANT_ENV') && ELEFANT_ENV !== 'config') {
			$conf = parse_ini_file ('conf/config.php', true);
			$conf = file_exists ('conf/' . ELEFANT_ENV . '.php')
				? array_replace_recursive (
					$conf,
					parse_ini_file ('conf/' . ELEFANT_ENV . '.php', true)
				  )
				: $conf;
		} else {
			$conf = parse_ini_file ('conf/config.php', true);
		}
	}
	if ($value) {
		if ($update !== null) {
			$conf[$section][$value] = $update;
		}
		return @$conf[$section][$value];
	}
	return @$conf[$section];
}

/**
 * Implements a simple authentication mechanism based on callbacks.
 * You provide a verifier function and a communication method function.
 * It then returns them like this:
 *
 *     method(verifier(user, pass));
 *
 * Implements HTTP Basic auth as a default method if none is given, and
 * uses the master account defined in `conf/global.php` if no verifier is
 * given.
 */
function simple_auth ($verifier = false, $method = false) {
	$verifier = ($verifier) ? $verifier : 'simple_auth_verifier';
	$method = ($method) ? $method : 'simple_auth_basic';
	return call_user_func ($method, $verifier);
}

/**
 * Default verifier for `simple_auth()`. This is meant to serve
 * as an example, and should be overridden with your own
 * implementation. Note its use of `conf()` variables that are
 * likely not set in a default Elefant installation.
 */
function simple_auth_verifier ($user, $pass) {
	if ($user == conf ('General', 'master_username') && $pass == conf ('General', 'master_password')) {
		return true;
	}
	return false;
}

/**
 * Default method for `simple_auth()`. Implements an HTTP basic
 * protocol.
 */
function simple_auth_basic ($callback) {
	if (! isset ($_SERVER['PHP_AUTH_USER']) || ! call_user_func ($callback, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
		header ('WWW-Authenticate: Basic realm="This Website"');
		header ('HTTP/1.0 401 Unauthorized');
		return false;
	}
	return true;
}

/**
 * Splits an SQL script into distinct queries which can be evaluated or
 * manipulated individually.
 */
function sql_split ($sql) {
	$out = array ('');
	$broken = preg_split ('/[\n\r]+/s', $sql);
	foreach ($broken as $row) {
		$row = trim ($row);
		if (strpos ($row, '#') === 0 || strpos ($row, '--') === 0 || empty ($row)) {
			continue;
		} elseif (preg_match ('/;$/', $row)) {
			$out[count ($out) - 1] .= substr ($row, 0, strlen ($row) - 1) . "\n";
			$out[] = '';
		} else {
			$out[count ($out) - 1] .= $row . "\n";
		}
	}
	if (empty ($out[count ($out) - 1])) {
		array_pop ($out);
	}
	return $out;
}

/**
 * Returns a file size formatted in a more human-friendly format, rounded
 * to the nearest GB, MB, KB, or byte.
 */
function format_filesize ($size = 0) {
	if ($size >= 1073741824) {
		return round ($size / 1073741824 * 10) / 10 . " GB";
	} elseif ($size >= 1048576) {
		return round ($size / 1048576 * 10) / 10 . " MB";
	} elseif ($size >= 1024) {
		return round ($size / 1024) . " KB";
	} else {
		return $size . " b";
	}
}

/**
 * Very basic browser detection. `$browser` can be one of:
 *
 * - msie, ie
 * - firefox, ff, moz
 * - chrome
 * - safari
 * - webkit
 * - opera
 * - opera mini
 * - opera mobi
 * - ios
 * - iphone
 * - ipad
 * - android
 * - iemobile
 * - webos
 * - blackberry
 * - googlebot
 * - bot
 * - mobile
 * - tablet
 * - desktop
 *
 * Notes:
 *
 * - `mobile` and `tablet` matches are not exhaustive, they only list the common
 *   platforms.
 *
 * - `desktop` means not a mobile or tablet device, and may be Linux, Mac, or Windows.
 *
 * - iPad and iPod are both reported as mobile devices in the user agent string,
 *   but `detect()` corrects for this in the case of the iPad.
 *
 * - Android reports as true for both mobile and tablet currently.
 *
 * - Some matches might be seen as false-positives, such as Chrome matching Safari.
 *   In these cases, look at other detection options. For example with Chrome, the
 *   rendering engine is Webkit, so better to simply use that.
 *
 * - No version detection. For IE version-specific needs, use conditional comments
 *   in your HTML, or use a string like `msie 10`.
 *
 * - Other lowercase strings that are not listed above can be used as well,
 *   for example `wap`, or `smartphone`.
 */
function detect ($browser) {
	$ua = strtolower ($_SERVER['HTTP_USER_AGENT']);
	$ver = '';

	// Normalize names
	$browser = ($browser === 'ie') ? 'msie' : $browser;
	$browser = ($browser === 'ff') ? 'firefox' : $browser;
	$browser = ($browser === 'moz') ? 'firefox' : $browser;

	if ($browser === 'mobile') {
		if (! preg_match ('/(iphone|ipod|android|opera mini|opera mobi|symb|phone|webos|blackberry|mobile)/', $ua)) {
			// No common mobile platform
			return false;
		} elseif (strpos ($ua, 'ipad') !== false) {
			// iPad should be separate from mobile
			return false;
		}
		return true;
	} elseif ($browser === 'tablet') {
		if (! preg_match ('/(ipad|android|tablet)/', $ua)) {
			// Not iPad, Android, or tablet
			return false;
		}
		return true;
	} elseif ($browser === 'ios') {
		if (! preg_match ('/(ipad|iphone|ipod)/', $ua)) {
			// Not iOS
			return false;
		}
		return true;
	} elseif ($browser === 'desktop') {
		if (detect ('mobile') || detect ('tablet')) {
			// Tablet or mobile
			return false;
		}
		return true;
	} elseif (strpos ($ua, $browser) === false) {
		return false;
	}
	return true;
}

/**
 * Recursively delete a folder and all its contents.
 * Handles hidden dot-files as well as regular files.
 */
function rmdir_recursive ($path) {
	if (preg_match ('|/\.+$|', $path)) {
		return;
	}
	return is_file ($path)
		? unlink ($path)
		: array_map ('rmdir_recursive', glob ($path . '/{,.}*', GLOB_BRACE)) == rmdir ($path);
}

/**
 * Fetch a remote URL using either cURL or fopen, depending
 * on which is available.
 */
function fetch_url ($url) {
	if (extension_loaded ('curl')) {
		$ch = curl_init ();
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt ($ch, CURLOPT_VERBOSE, 0);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($ch, CURLOPT_FAILONERROR, 0);
		curl_setopt ($ch, CURLOPT_URL, $url);
		$res = curl_exec ($ch);
		curl_close ($ch);
		return $res;
	}
	return file_get_contents ($url);
}

?>