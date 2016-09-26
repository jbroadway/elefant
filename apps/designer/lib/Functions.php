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

/**
 * Does the text contain a call to any invalid PHP functions,
 * which should not be used inside of a template. These include
 * things like eval(), getenv(), and many more.
 */
function invalid_php_functions ($text) {
	if (preg_match ('/\b(apache_child_terminate|apache_setenv|assert|assert_options|bzopen|call_user_func|call_user_func_array|chgrp|chmod|chown|copy|create_function|define_syslog_variables|disk_free_space|disk_total_space|diskfreespace|escapeshellarg|escapeshellcmd|eval|exec|exif_imagetype|exif_read_data|exif_thumbnail|extract|file|file_exists|file_get_contents|file_put_contents|fileatime|filectime|filegroup|fileinode|filemtime|fileowner|fileperms|filesize|filetype|fopen|fputs|fsockopen|ftp_get|ftp_nb_get|ftp_nb_put|ftp_put|ftp_raw|ftp_rawlist|fwrite|get_cfg_var|get_current_user|get_meta_tags|getcwd|getenv|getimagesize|getlastmo|getmygid|getmyinode|getmypid|getmyuid|glob|gzfile|gzopen|hash_file|hash_hmac_file|hash_update_file|header|highlight_file|image2wbmp |imagecreatefromgif|imagecreatefromjpeg|imagecreatefrompng|imagecreatefromwbmp|imagecreatefromxbm|imagecreatefromxpm|imagegd   |imagegd2  |imagegif  |imagejpeg |imagepng  |imagewbmp  |imagexbm  |include|include_once|ini_set|invoke|invokeArgs|iptcembed|is_dir|is_executable|is_file|is_link|is_readable|is_uploaded_file|is_writable|is_writeable|lchgrp|lchown|link|linkinfo|lstat|mail|md5_file|mkdir|move_uploaded_file|mysql_connect|mysql_pconnect|mysqli_connect|ob_start|ob_end_flush|ob_get_contents|openlog|parse_ini_file|parse_str|passthru|pathinfo|pcntl_exec|pfsockopen|php_strip_whitespace|phpinfo|php_uname|popen|posix_getlogin|posix_kill|posix_mkfifo|posix_mkfifo|posix_setpgid|posix_setsid|posix_setuid|posix_ttyname|preg_replace_callback|proc_close|proc_get_status|proc_nice|proc_open|proc_terminate|putenv|read_exif_data|readfile|readgzfile|readlink|realpath|ReflectionFunction|register_shutdown_function|register_tick_function|rename|require|require_once|rmdir|set_error_handler|set_exception_handler|sha1_file|shell_exec|show_source|SplFileObject|stat|symlink|syslog|system|tempnam|tmpfile|touch|unlink|xmlrpc_entity_decode)\s*?\(/s', $text, $regs)) {
		return true;
	}
	return false;
}
