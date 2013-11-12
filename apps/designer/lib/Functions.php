<?php

/**
 * Fetches the short name of the layout template.
 */
function basename_html ($f) {
	$name = basename ($f, '.html');
	$parts = explode ('/', $f);
	array_pop ($parts);
	$folder = array_pop ($parts);
	if ($folder === 'layouts' || $folder === $name) {
		return $name;
	}
	return $folder . '/' . $name;
}

/**
 * Parse a Github URL and return an array with the username and repository.
 * Also accepts repository names using the shorter form `username/repository`.
 * Returns false on failure.
 */
function github_parse_url ($url) {
	if (preg_match ('|^([a-z0-9_-]+)/([a-z0-9_-]+)$|i', $url, $regs)) {
		return array ($regs[1], $regs[2]);
	}

	if (strpos ($url, 'github') === false) {
		return false;
	}

	if (preg_match ('|([a-z0-9_-]+)/([a-z0-9_-]+)\.git$|i', $url, $regs)) {
		return array ($regs[1], $regs[2]);
	}

	return false;
}

/**
 * Checks a URL to see if it's a zip file, accounting for GitHub's
 * zipball URLs that don't end in .zip.
 */
function github_is_zip ($url) {
	if (preg_match ('/^https?:\/\/.*\.zip$/i', $url)) {
		return true;
	}
	if (preg_match ('/^https?:\/\/github\.com\/.*zipball.*/i', $url)) {
		return true;
	}
	return false;
}

/**
 * Returns whether the specified URL is a valid Github URL.
 * Used for validating the input of the app/theme installer.
 * Also returns true if it's a link to a zip file.
 */
function github_is_valid_url ($url) {
	if (github_parse_url ($url) === false) {
		if (github_is_zip ($url)) {
			return true;
		}
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