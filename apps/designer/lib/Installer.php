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
		if (! isset ($config->type)) {
			self::$error = 'No type specified';
			return false;
		}

		if (! in_array ($config->type, array ('theme', 'app'))) {
			// No type or invalid type specified
			self::$error = 'Invalid type';
			return false;
		}

		if (! isset ($config->folder)) {
			self::$error = 'No folder specified';
			return false;
		}

		if (! preg_match ('/^[a-z0-9_-]+$/i', $config->folder)) {
			// No folder or invalid name (e.g., spaces)
			self::$error = 'Invalid folder name';
			return false;
		}

		if (! isset ($config->version)) {
			// Version is required
			self::$error = 'No version specified';
			return false;
		}

		if (! isset ($config->repository) && ! isset ($config->website)) {
			// Repository or website required
			self::$error = 'Repository or website required';
			return false;
		}

		return true;
	}

	/**
	 * Override this method to provide the installation details.
	 */
	public static function install ($source) {
		return false;
	}
}

?>