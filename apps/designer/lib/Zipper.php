<?php

/**
 * Provides a wrapper around ZipArchive with PclZip as a fallback
 * option.
 */
class Zipper {
	public static $folder = 'cache/zip';
	/**
	 * Unzip a file into the specified directory. Throws a RuntimeException
	 * if the extraction failed.
	 */
	public static function unzip ($source, $base = null) {
		$base = $base ? $base : self::$folder;

		@ini_set ('memory_limit', '256M');
	
		if (! is_dir ($base)) {
			mkdir ($base);
			chmod ($base, 0777);
		}
	
		if (class_exists ('ZipArchive')) {
			// use ZipArchive
			$zip = new ZipArchive;
			$res = $zip->open ($source);
			if ($res === true) {
				$zip->extractTo ($base);
				$zip->close ();
			} else {
				throw new RuntimeException ('Could not open zip file [ZipArchive].');
			}
		} else {
			// use PclZip
			$zip = new PclZip ($source);
			if ($zip->extract (PCLZIP_OPT_PATH, $base) === 0) {
				throw new RuntimeException ('Could not extract zip file [PclZip].');
			}
		}
		return true;
	}

	/**
	 * Find the folder that the zip created.
	 */
	public static function find_folder ($source, $base = null) {
		$base = $base ? $base : self::$folder;

		$folder = preg_replace ('/\.zip$/i', '', basename ($source));

		$files = glob ($base . '/*');
		foreach ($files as $file) {
			if (is_dir ($file) && basename ($file) === $folder) {
				return $file;
			}
		}
		return false;
	}

	/**
	 * Remove all files and keep the cache folder clean.
	 */
	public static function clean () {
		if (file_exists (self::$folder)) {
			rmdir_recursive (self::$folder);
		}
	}
}