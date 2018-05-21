<?php

/**
 * Takes a length and returns a random string of characters of that
 * length for use in passwords. String may contain any number, lower
 * or uppercase letters, or common symbols.
 */
function generate_password ($length, $include_symbols = true) {
	$list = ($include_symbols)
		? '123467890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_-+=~:;|<>[]{}?"\''
		: '123467890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	$pass = '';
	while (strlen ($pass) < $length) {
		$pass .= substr ($list, mt_rand (0, strlen ($list)), 1);
	}
	return $pass;
}

/**
 * Determine which patches/db updates need to be run.
 * Note that this imposes limits on the version numbering
 * of Elefant, specifically:
 *
 * - Minor versions can go up to 10
 * - Bug fix numbers can go up to 40
 *
 * So the highest release number for the 1.x series is 1.10.40
 * before rolling over to 2.0.0.
 */
function cli_get_versions ($current, $latest) {
	$versions = array ();
	$files = glob ('conf/updates/elefant-' . $current . '-*.patch');
	while ($current !== $latest) {
		$files = glob ('conf/updates/elefant-' . $current . '-*.patch');
		if (count ($files) > 0) {
			$script = preg_replace ('/\.patch$/', '.sql', $files[0]);
			$versions[] = array (
				'patch' => $files[0],
				'script' => file_exists ($script) ? $script : false
			);
		}

		list ($major, $minor, $fix) = explode ('.', $current);
		$fix++;
		if ($fix > 40) {
			$fix = 0;
			$minor++;
		}
		if ($minor > 10) {
			$major++;
			$minor = 0;
		}
		$current = $major . '.' . $minor . '.' . $fix;
	}
	return $versions;
}
