<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Used by the file manager API to verify files and folders.
 */
class FileManager {
	/**
	 * Verify that the specified folder is valid, and exists
	 * inside a certain root folder.
	 */
	public static function verify_folder ($path, $root = false) {
		$root = ($root) ? rtrim ($root) : getcwd () . '/files';
		$path = trim ($path, '/');
		if (strpos ($path, '..') !== false) {
			return false;
		}
		if (! @is_dir ($root . '/' . $path)) {
			return false;
		}
		return true;
	}

	/**
	 * Verify that the specified file is valid, and exists
	 * inside a certain root folder.
	 */
	public static function verify_file ($path, $root = false) {
		$root = ($root) ? rtrim ($root) : getcwd () . '/files';
		$path = trim ($path, '/');
		if (strpos ($path, '..') !== false) {
			return false;
		}
		if (! @file_exists ($root . '/' . $path)) {
			return false;
		}
		return true;
	}

	/**
	 * Verify that a folder name contains only a-z, A-Z, 0-9,
	 * underscores, and dashes.
	 */
	public static function verify_folder_name ($name) {
		if (! preg_match ('/^[a-zA-Z0-9_-]+$/', $name)) {
			return false;
		}
		return true;
	}

	/**
	 * Verify that a file name contains only a-z, A-Z, 0-9,
	 * underscores, and dashes, and a dot.
	 */
	public static function verify_file_name ($name) {
		if (! preg_match ('/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+$/', $name)) {
			return false;
		}
		return true;
	}

	/**
	 * Helper for sorting files by name. For use with `usort()`.
	 */
	public static function fsort ($a, $b) {
		return strcmp ($a['name'], $b['name']);
	}
}

?>