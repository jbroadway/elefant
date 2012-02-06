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
			self::$error = i18n_get ('Could not unzip the file.');
			return false;
		}

		$folder = 'cache/zip/' . preg_replace ('/\.zip$/i', '', $source['name']);

		// Get config and verify it
		if (! file_exists ($folder . '/elefant.json')) {
			self::$error = i18n_get ('Verification failed: No configuration file found.');
			return false;
		}

		$conf = json_decode (file_get_contents ($folder . '/elefant.json'));
		if ($conf === false) {
			self::$error = i18n_get ('Verification failed: Invalid configuration file.');
			return false;
		}

		if (! self::verify ($conf)) {
			// self::$error already set by verify()
			return false;
		}

		// Move files over
		if ($conf->type === 'app') {
			if (! rename ($folder, 'apps/' . $conf->folder)) {
				self::$error = i18n_get ('Unable to write to apps folder.');
				return false;
			}
			chmod_recursive ('apps/' . $conf->folder, 0777);
		} else {
			if (! rename ($folder, 'layouts/' . $conf->folder)) {
				self::$error = i18n_get ('Unable to write to layouts folder.');
			}
			chmod_recursive ('layouts/' . $conf->folder, 0777);
		}
		return $conf;
	}
}

?>