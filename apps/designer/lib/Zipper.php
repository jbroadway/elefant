<?php

/**
 * Provides a wrapper around ZipArchive with PclZip as a fallback
 * option.
 */
class Zipper {
	/**
	 * Unzip a file into the specified directory. Throws a RuntimeException
	 * if the extraction failed.
	 */
	public static function unzip ($file, $to = 'cache/zip') {
		@ini_set ('memory_limit', '256M');
	
		if (! is_dir ($to)) {
			mkdir ($to);
			chmod ($to, 0777);
		}
	
		if (class_exists ('ZipArchive')) {
			// use ZipArchive
			$zip = new ZipArchive;
			$res = $zip->open ($file);
			if ($res === true) {
				$zip->extractTo ($to);
				$zip->close ();
			} else {
				throw new RuntimeException ('Could not open zip file [ZipArchive].');
			}
		} else {
			// use PclZip
			$zip = new PclZip ($file);
			if ($zip->extract (PCLZIP_OPT_PATH, $to) === 0) {
				throw new RuntimeException ('Could not extract zip file [PclZip].');
			}
		}
		return true;
	}
}