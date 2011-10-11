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
 * Get a global configuration value from any namespace,
 * without calling `global $conf` first. Useful in templates
 * as well.
 */
function conf ($section, $value = false) {
	if ($value) {
		return $GLOBALS['conf'][$section][$value];
	}
	return $GLOBALS['conf'][$section];
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
	if (! $verifier) {
		$verifier = 'simple_auth_verifier';
	}
	if (! $method) {
		$method = 'simple_auth_basic';
	}
	return call_user_func ($method, $verifier);
}

/**
 * Default verifier for `simple_auth()`. This is meant to serve
 * as an example, and should be overridden with your own
 * implementation.
 */
function simple_auth_verifier ($user, $pass) {
	global $conf;
	if ($user == $conf['General']['master_username'] && $pass == $conf['General']['master_password']) {
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

?>