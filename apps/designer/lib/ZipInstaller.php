<?php

require_once ('apps/designer/lib/Functions.php');

/**
 * Installs an app or theme from an uploaded zip file.
 */
class ZipInstaller extends Installer {
	/**
	 * Requires the entry from $_FILES of a zip file.
	 */
	public static function install ($source) {
		try {
			Zipper::unzip ($source['tmp_name']);
		} catch (Exception $e) {
			self::$error = __ ('Could not unzip the file.');
			return false;
		}

		$folder = ZipInstaller::find_folder ('cache/zip');

		// Get config and verify it
		if (! file_exists ($folder . '/elefant.json')) {
			self::$error = __ ('Verification failed: No configuration file found.');
			return false;
		}

		$conf = json_decode (file_get_contents ($folder . '/elefant.json'));
		if ($conf === false) {
			self::$error = __ ('Verification failed: Invalid configuration file.');
			return false;
		}

		if (! self::verify ($conf)) {
			// self::$error already set by verify()
			return false;
		}

		// Move files over
		if ($conf->type === 'app') {
			if (! rename ($folder, 'apps/' . $conf->folder)) {
				self::$error = __ ('Unable to write to apps folder.');
				return false;
			}
			chmod_recursive ('apps/' . $conf->folder, 0777);
		} else {
			if (! rename ($folder, 'layouts/' . $conf->folder)) {
				self::$error = __ ('Unable to write to layouts folder.');
			}
			chmod_recursive ('layouts/' . $conf->folder, 0777);
		}

		// Remove the original zip file
		@unlink ($source['tmp_name']);

		return $conf;
	}

	/**
	 * Find the folder that the zip created.
	 */
	public static function find_folder ($base) {
		$files = glob ($base . '/*');
		foreach ($files as $file) {
			if (is_dir ($file)) {
				return $file;
			}
		}
		return false;
	}

	/**
	 * Remove all files and keep the cache folder clean.
	 */
	public static function clean () {
		if (file_exists ('cache/zip')) {
			rmdir_recursive ('cache/zip');
		}
	}

	/**
	 * Fetch a zip file from a link.
	 */
	public static function fetch ($url) {
		$path = parse_url ($url, PHP_URL_PATH);
		if (strpos ($path, '/zipball/') !== false) {
			// Fix zip file links from Github
			$path = current (explode ('/zipball/', $path)) . '.zip';
		}
		$base = basename ($path);
		$tmp = 'cache/zip/' . $base;

		if (! is_dir ('cache/zip')) {
			mkdir ('cache/zip');
			chmod ('cache/zip', 0777);
		}

		$ch = curl_init ();
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt ($ch, CURLOPT_VERBOSE, 0);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt ($ch, CURLOPT_MAXREDIRS, 3);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($ch, CURLOPT_FAILONERROR, 0);
		curl_setopt ($ch, CURLOPT_URL, $url);
		$res = curl_exec ($ch);
		curl_close ($ch);
		if ($res === false) {
			self::$error = __ ('Failed to retrieve the file at the specified link.');
			return false;
		}

		if (! file_put_contents ($tmp, $res)) {
			self::$error = __ ('Unable to write to cache folder.');
			return false;
		}
		chmod ($tmp, 0777);

		return array (
			'tmp_name' => $tmp,
			'name' => $base
		);
	}
}

?>