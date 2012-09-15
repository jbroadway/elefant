<?php

/**
 * Takes a length and returns a random string of characters of that
 * length for use in passwords. String may contain any number, lower
 * or uppercase letters, or common symbols.
 */
function generate_password ($length) {
	$list = '123467890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_-+=~:;|<>[]{}?"\'';
	$pass = '';
	while (strlen ($pass) < $length) {
		$pass .= substr ($list, mt_rand (0, strlen ($list)), 1);
	}
	return $pass;
}

?>