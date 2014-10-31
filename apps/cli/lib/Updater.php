<?php

/**
 * Contains the logic for fetching and verifying update files.
 */
class Updater {
	/**
	 * The prefix for the update server.
	 */
	public static $updates = 'https://raw.githubusercontent.com/jbroadway/elefant-updates/master/';
	
	/**
	 * The prefix for the checksum server.
	 */
	public static $checksums = 'https://raw.githubusercontent.com/elefantcms/checksums/master/';
	
	/**
	 * Contains any errors from the last `fetch()` call.
	 */
	public static $error = false;

	/**
	 * Fetches an update file and verifies its authenticity before returning
	 * the file contents. Returns false on failure.
	 */
	public static function fetch ($path) {
		self::$error = false;

		$file_url = self::$updates . $path;
		$sha_url = self::$checksums . $path . '.sha';

		$file = fetch_url ($file_url);
		if (! $file) {
			self::$error = 'Unable to fetch file.';
			return false;
		}

		$sha = fetch_url ($sha_url);
		if (! $sha) {
			self::$error = 'Unable to fetch checksum file.';
			return false;
		}

		if (! self::test_checksum ($file, $sha)) {
			self::$error = 'Checksum failed!';
			return false;
		}

		return $file;
	}

	/**
	 * Returns the last error from `fetch()`, or false if no error occurred.
	 */
	public static function error () {
		return self::$error;
	}

	/**
	 * Generates a SHA-512 checksum on the supplied data.
	 */
	public static function checksum ($data) {
		return hash ('sha512', $data);
	}

	/**
	 * Generates a SHA-512 checksum on the supplied file.
	 */
	public static function checksum_file ($file) {
		return hash_file ('sha512', $file);
	}

	/**
	 * Compares the supplied data and checksum for authenticity.
	 */
	public static function test_checksum ($data, $checksum) {
		$check2 = self::checksum ($data);
		if ($check2 !== $checksum) {
			return false;
		}
		return true;
	}
}
