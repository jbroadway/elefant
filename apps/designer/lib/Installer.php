<?php

/**
 * Base class to extend to write installers for Elefant.
 */
class Installer {
	/**
	 * If there's an error, this should contain the message.
	 */
	static $error = false;

	/**
	 * Verifies it's an Elefant app or theme with the required
	 * config info.
	 */
	public static function verify ($config) {
		if (! isset ($config->name)) {
			self::$error = __ ('Verification failed: No name specified');
			return false;
		}

		if (! isset ($config->type)) {
			self::$error = __ ('Verification failed: No type specified.');
			return false;
		}

		if (! in_array ($config->type, array ('theme', 'app'))) {
			// No type or invalid type specified
			self::$error = __ ('Verification failed: Invalid type.');
			return false;
		}

		if (! isset ($config->folder)) {
			self::$error = __ ('Verification failed: No folder specified');
			return false;
		}

		if (! preg_match ('/^[a-z0-9\._-]+$/i', $config->folder)) {
			// No folder or invalid name (e.g., spaces)
			self::$error = __ ('Verification failed: Invalid folder name.');
			return false;
		}

		if (! isset ($config->version)) {
			// Version is required
			self::$error = __ ('Verification failed: No version specified.');
			return false;
		}

		if (! isset ($config->repository) && ! isset ($config->website)) {
			// Repository or website required
			self::$error = __ ('Verification failed: Repository or website required.');
			return false;
		}

		if (isset ($config->requires) && ! self::verify_requires ($config->requires)) {
			// Site failed to meet minimum requirements (PHP or Elefant version)
			return false;
		}

		// Check that it's not overwriting an existing app or theme
		if ($config->type == 'theme' && file_exists ('layouts/' . $config->folder)) {
			self::$error = __ ('A theme by this name is already installed.');
			return false;
		} elseif ($config->type == 'app' && file_exists ('apps/' . $config->folder)) {
			self::$error = __ ('An app by this name is already installed.');
			return false;
		}

		return true;
	}

	/**
	 * Verifies the site meets any requirements specified by the app or theme.
	 */
	public static function verify_requires ($requires) {
		if (isset ($requires->php) && version_compare (PHP_VERSION, $requires->php) < 0) {
			self::$error = __ ('Verification failed: This install requires PHP %s or newer.', $requires->php);
			return false;
		}

		if (isset ($requires->elefant) && version_compare (ELEFANT_VERSION, $requires->elefant) < 0) {
			self::$error = __ ('Verification failed: This install requires Elefant %s or newer.', $requires->elefant);
			return false;
		}

		return true;
	}

	/**
	 * Override this method to provide the installation details.
	 * Should return false on failure and the config object on success.
	 */
	public static function install ($source) {
		return false;
	}
}
