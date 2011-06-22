<?php

/**
 * Autoloader for classes. Checks in lib and models folders.
 */
function __autoload ($class) {
	if (strpos ($class, '\\') !== false) {
		list ($app, $class) = explode ('\\', $class, 2);
		if (@file_exists ('apps/' . $app . '/lib/' . $class . '.php')) {
			require_once ('apps/' . $app . '/lib/' . $class . '.php');
			return true;
		} elseif (@file_exists ('apps/' . $app . '/models/' . $class . '.php')) {
			require_once ('apps/' . $app . '/models/' . $class . '.php');
			return true;
		}
	} elseif (file_exists ('lib/' . $class . '.php')) {
		require_once ('lib/' . $class . '.php');
		return true;
	} else {
		$res = glob ('apps/*/{models,lib}/' . $class . '.php', GLOB_BRACE);
		if (is_array ($res) && count ($res) > 0) {
			require_once ($res[0]);
			return true;
		}
	}
	return false;
}

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
function conf ($section, $value) {
	return $GLOBALS['conf'][$section][$value];
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