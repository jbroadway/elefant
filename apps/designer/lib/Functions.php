<?php

/**
 * Parse a Github URL and return an array with the username and repository.
 * Returns false on failure.
 */
function github_parse_url ($url) {
	if (strpos ($url, 'github') === false) {
		return false;
	}

	if (preg_match ('|([a-z0-9_-]+)/([a-z0-9_-]+)\.git$|i', $url, $regs)) {
		return array ($regs[1], $regs[2]);
	}

	return false;
}

/**
 * Returns whether the specified URL is a valid Github URL.
 */
function github_is_valid_url ($url) {
	if (github_parse_url ($url) === false) {
		return false;
	}

	return true;
}

/**
 * Recursively change the permissions on a folder and all its contents.
 * Handles hidden dot-files as well as regular files.
 */
function chmod_recursive ($path, $mode = false) {
	if (preg_match ('|/\.+$|', $path)) {
		return;
	}

	static $_mode = false;
	$_mode = ($_mode === false && $mode !== false) ? $mode : $_mode;
	$mode = $_mode;

	return is_file ($path)
		? chmod ($path, $mode)
		: array_map ('chmod_recursive', glob ($path . '/{,.}*', GLOB_BRACE)) == chmod ($path, $mode);
}

?>