<?php

/**
 * Autoloader for classes. Checks in lib and models folders.
 */
function __autoload ($class) {
	if (file_exists ('lib/' . $class . '.php')) {
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
 * Wraps a print_r() or var_dump() of the given $value with a set of <pre></pre>
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
 * Implements a simple authentication mechanism based on callbacks.
 * You provide a verifier function and a communication method function.
 * It then returns them like this:
 *
 *   method(verifier(user, pass));
 *
 * Implements HTTP Basic auth as a default method if none is given, and
 * uses the master account defined in conf/global.php if no verifier is
 * given.
 */
function simple_auth ($verifier = false, $method = false) {
	if (! $verifier) {
		$verifier = function ($user, $pass) {
			global $conf;
			if ($user == $conf['General']['master_username'] && $pass == $conf['General']['master_password']) {
				return true;
			}
			return false;
		};
	}
	if (! $method) {
		$method = function ($callback) {
			if (! isset ($_SERVER['PHP_AUTH_USER']) || ! call_user_func ($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
				header ('WWW-Authenticate: Basic realm="This Website"');
				header ('HTTP/1.0 401 Unauthorized');
				//echo 'You must be logged in to access these pages.';
				//exit;
				return false;
			}
			return true;
		};
	}
	return call_user_func ($method, $verifier);
}

/**
 * Splits an SQL script into distinct queries which can be evaluated or
 * manipulated individually.
 *
 * @access public
 * @param string
 * @return array
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

?>