<?php

/**
 * This script generates an encrypted version of a password
 * or string given to it.
 *
 * Usage:
 *
 *     Usage: php conf/genpass.php password
 */

if (php_sapi_name () !== 'cli') {
	die ("For use on the command line only.\n");
}

if ($argc == 1) {
	die ("Usage: php conf/genpass.php password\n");
}

function encrypt_pass ($plain) {
	$base = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$salt = '$1$';
	for ($i = 0; $i < 9; $i++) {
		$salt .= $base[rand (0, 61)];
	}
	return crypt ($plain, $salt . '$');
}

echo encrypt_pass ($argv[1]) . "\n";

?>