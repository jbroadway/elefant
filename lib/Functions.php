<?php

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
 * Implements HTTP Basic auth using a callback to determine if the
 * username and password are valid. If no callback is given, then
 * it uses the global 
 */
function auth_basic ($callback = false) {
	if (! $callback) {
		$callback = function ($user, $pass) {
			global $conf;
			if ($user == $conf['General']['master_username'] && $pass == $conf['General']['master_password']) {
				return true;
			}
			return false;
		};
	}
	if (! isset ($_SERVER['PHP_AUTH_USER']) || ! $callback ($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
		header ('WWW-Authenticate: Basic realm="This Website"');
		header ('HTTP/1.0 401 Unauthorized');
		echo 'You must be logged in to access these pages.';
		exit;
	}
}

?>