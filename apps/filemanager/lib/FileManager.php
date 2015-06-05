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
 * Provides an API for managing common file operations such as listing directory
 * contents, renaming, and deleting files. Using this class, extended file
 * properties will be correctly re-linked to files that are renamed or moved,
 * and file and folder names and paths will be verified to be correct.
 *
 * Also note that file and folder paths are specified relative to `FileManager::root()`.
 *
 * Usage:
 *
 *     <?php
 *     
 *     // Get listing of files/homepage directory
 *     $list = FileManager::dir ('homepage');
 *     
 *     // Delete files/homepage/photo1.jpg
 *     $res = FileManager::unlink ('homepage/photo1.jpg');
 *     
 *     ?>
 */
class FileManager {
	/**
	 * The path to the root directory to store files.
	 */
	public static $root = null;

	/**
	 * The web path to the root directory.
	 */
	public static $webroot = null;

	/**
	 * The error message if an error occurs in one of the static methods.
	 */
	public static $error;

	/**
	 * Returns the $root. Sets $root if not yet set.
	 */
	public static function root () {
                self::$webroot = self::$webroot ? self::$webroot : '/' . conf('Paths','filemanager_path') . '/';
		if (self::$root === null) {
			self::$root = getcwd () . '/' . conf('Paths','filemanager_path') . '/';
		}
		return self::$root;
	}

	/**
	 * Adds the $webroot to a file/folder path, if not present.
	 * Examples:
	 *
	 *     foo.txt  -> /files/foo.txt
	 *     /foo.txt -> /files/foo.txt
	 */
	public static function add_webroot ($path) {
                self::$webroot = self::$webroot ? self::$webroot : '/' . conf('Paths','filemanager_path') . '/';
		$path = (strpos ($path, '/') === 0) ? $path : '/' . $path;
		if (! preg_match ('/^' . preg_quote (self::$webroot, '/') . '/', $path)) {
			$path = self::$webroot . ltrim ($path, '/');
		}
		return $path;
	}

	/**
	 * Removes the $webroot from a file/folder path.
	 * Examples:
	 *
	 *     /files/foo.txt -> foo.txt
	 *     files/foo.txt  -> foo.txt
	 */
	public static function strip_webroot ($path) {
                self::$webroot = self::$webroot ? self::$webroot : '/' . conf('Paths','filemanager_path') . '/';
		$root = preg_quote (self::$webroot, '/');
		$root = preg_replace ('|^\\\/|', '\\/?', $root);
		return preg_replace ('/^' . $root . '/', '', $path);
	}

	/**
	 * Returns the last error message.
	 */
	public static function error () {
		return self::$error;
	}

	/**
	 * List all directories and files in a directory. Returns an array
	 * with 'dirs' and 'files'. Each directory has a 'name', 'path',
	 * and 'mtime'. Each file also has 'fsize'.
	 */
	public static function dir ($path = '') {
		if (! self::verify_folder ($path)) {
			self::$error = __ ('Invalid folder name');
			return false;
		}

		$d = dir (self::root () . $path);
		if (! $d) {
			self::$error = __ ('Unable to read folder');
			return false;
		}

		$out = array ('dirs' => array (), 'files' => array ());
		while (false !=  ($entry = $d->read ())) {
			if (preg_match ('/^\./', $entry)) {
				continue;
			} elseif (is_dir (self::root () . $path . '/' . $entry)) {
				$out['dirs'][] = array (
					'name' => $entry,
					'path' => ltrim ($path . '/' . $entry, '/'),
					'mtime' => filemtime (self::root () . $path . '/' . $entry)
				);
			} else {
				$out['files'][] = array (
					'name' => $entry,
					'path' => ltrim ($path . '/' . $entry, '/'),
					'mtime' => filemtime (self::root () . $path . '/' . $entry),
					'fsize' => filesize (self::root () . $path . '/' . $entry)
				);
			}
		}
		$d->close ();
		usort ($out['dirs'], array ('FileManager', 'fsort'));
		usort ($out['files'], array ('FileManager', 'fsort'));
		return $out;
	}

	/**
	 * Delete a file.
	 */
	public static function unlink ($file) {
		if (self::verify_folder ($file)) {
			self::$error = __ ('Unable to delete folders');
			return false;
		} elseif (! self::verify_file ($file)) {
			self::$error = __ ('File not found');
			return false;
		} elseif (! unlink (self::root () . $file)) {
			self::$error = __ ('Unable to delete') . ' ' . $file;
			return false;
		}
		self::prop_delete ($file);
		return true;
	}

	/**
	 * Rename a file or folder.
	 */
	public static function rename ($file, $new_name) {
		if (self::verify_folder ($file)) {
			if (! self::verify_folder_name ($new_name)) {
				self::$error = __ ('Invalid folder name');
				return false;
			}
			$parts = explode ('/', $file);
			$old = array_pop ($parts);
			$new = preg_replace ('/' . preg_quote ($old) . '$/', $new_name, $file);
			if (! rename (self::root () . $file, self::root () . $new)) {
				self::$error = __ ('Unable to rename') . ' ' . $file;
				return false;
			}
			self::prop_rename ($file, $new, true);
			return true;
		} elseif (self::verify_file ($file)) {
			if (! self::verify_file_name ($new_name)) {
				self::$error = __ ('Invalid file name');
				return false;
			}
			$parts = explode ('/', $file);
			$old = array_pop ($parts);
			$new = preg_replace ('/' . preg_quote ($old) . '$/', $new_name, $file);
			if (! rename (self::root () . $file, self::root () . $new)) {
				self::$error = __ ('Unable to rename') . ' ' . $file;
				return false;
			}
			FileManager::prop_rename ($file, $new);
			return true;
		}
		self::$error = __ ('File not found');
		return false;
	}

	/**
	 * Move a file to a new folder.
	 */
	public static function move ($file, $folder) {
		if (! self::verify_file ($file)) {
			self::$error = __ ('File not found');
			return false;
		}

		if (! self::verify_folder ($folder)) {
			self::$error = __ ('Invalid folder');
			return false;
		}

		$new = $folder . '/' . basename ($file);
		$new = ltrim ($new, '/');
		if (! rename (self::root () . $file, self::root () . $new)) {
			self::$error = __ ('Unable to move') . ' ' . $file;
			return false;
		}

		self::prop_rename ($file, $new);
		return true;
	}

	/**
	 * Touch a file. If it exists, updates its modification
	 * time. If not, creates a blank file.
	 */
	public static function touch ($file) {
		if (! self::verify_file ($file)) {
			$basename = basename ($file);
			$path = pathinfo ($file, PATHINFO_DIRNAME);
			if (! self::verify_folder ($path)) {
				self::$error = __ ('Invalid folder');
				return false;
			}
			if (! self::verify_file_name ($basename)) {
				self::$error = __ ('Invalid file name');
				return false;
			}
		}

		return touch (self::root () . $file);
	}

	/**
	 * Make a new folder.
	 */
	public static function mkdir ($folder) {
		$parts = explode ('/', $folder);
		$newdir = array_pop ($parts);
		$path = preg_replace ('/\/?' . preg_quote ($newdir) . '$/', '', $folder);
		if (! self::verify_folder ($path)) {
			self::$error = __ ('Invalid location');
			return false;
		} elseif (! self::verify_folder_name ($newdir)) {
			self::$error = __ ('Invalid folder name');
			return false;
		} elseif (is_dir (self::root () . $folder)) {
			self::$error = __ ('Folder already exists') . ' ' . $folder;
			return false;
		} elseif (! mkdir (self::root () . $folder)) {
			self::$error = __ ('Unable to create folder') . ' ' . $folder;
			return false;
		}
		chmod (self::root () . $folder, 0777);
		return true;
	}

	/**
	 * Remove a folder. The folder must be empty, or recursive
	 * must be set to true to remove non-empty folders.
	 */
	public static function rmdir ($folder, $recursive = false) {
		if (! self::verify_folder ($folder)) {
			self::$error = __ ('Invalid folder name');
			return false;
		}

		$list = self::dir ($folder);
		if (! $list) {
			self::$error = __ ('Unable to verify folder');
			return false;
		}
		
		if (! $recursive) {
			if (count ($list['dirs']) > 0 || count ($list['files']) > 0) {
				self::$error = __ ('Folder must be empty');
				return false;
			}
			if (! rmdir (self::root () . $folder)) {
				self::$error = __ ('Unable to delete folder');
				return false;
			}
			return true;
		}

		return self::rmdir_recursive (self::root () . $folder);
	}

	/**
	 * Handles recursively deleting folders for `FileManager::rmdir()`.
	 */
	private static function rmdir_recursive ($path) {
		if (preg_match ('|/\.+$|', $path)) {
			return;
		}
		return is_file ($path)
			? unlink ($path)
			: array_map (array ('FileManager', 'rmdir_recursive'), glob ($path . '/{,.}*', GLOB_BRACE)) == rmdir ($path);
	}

	/**
	 * Returns a list of folders recursively under the specified
	 * folder path.
	 */
	public static function list_folders ($path = '') {
		$folders = array ();
                $root = conf('Paths','filemanager_path');
		if (! empty ($path)) {
			$rpath = $root . "/" . $path;
			$epath = $path . '/';
		} else {
			$rpath = $root;
			$epath = '';
		}
		$d = dir ($rpath);
		if (! $d) {
			return array ();
		}
		while (false !== ($file = $d->read ())) {
			$files[] = $file;
		}
		$d->close ();

		foreach ($files as $file) {
			if (strpos ($file, '.') === 0 || ! @is_dir ($rpath . '/' . $file)) {
				continue;
			}
			$folders[] = $epath . $file;
			$subs = self::list_folders ($epath . $file);
			foreach ($subs as $sub) {
				$folders[] = $sub;
			}
		}
		return $folders;
	}

	/**
	 * Verify that the specified folder is valid, and exists
	 * inside a certain root folder.
	 */
	public static function verify_folder ($path, $root = false) {
		$root = ($root) ? rtrim ($root) : rtrim (self::root ());
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
		$root = ($root) ? rtrim ($root) : rtrim (self::root ());
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
		if (! preg_match ('/^[a-zA-Z0-9 _-]+$/', $name)) {
			return false;
		}
		return true;
	}

	/**
	 * Verify that a file name contains only a-z, A-Z, 0-9,
	 * underscores, and dashes, and a dot.
	 */
	public static function verify_file_name ($name) {
		if (! preg_match ('/^[a-zA-Z0-9 _-]+\.[a-zA-Z0-9_-]+$/', $name)) {
			return false;
		}
		if (preg_match ('/\.php$/i', $name)) {
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

	/**
	 * Fetch all of the properties for the specified file.
	 */
	public static function props ($file) {
		return DB::pairs ('select prop, value from #prefix#filemanager_prop where file = ?', $file);
	}

	/**
	 * Get or set a property for the specified file.
	 * Can also retrieve an array of a property for a
	 * list of files if `$file` is an array.
	 */
	public static function prop ($file, $prop, $value = null) {
		if ($value !== null) {
			// takes an extra select query, but works cross-database
			$res = self::prop ($file, $prop);
			if ($res === $value) {
				return $value;
			} elseif ($res === false || $res === null) {
				// doesn't exist yet
				if (! DB::execute (
					'insert into #prefix#filemanager_prop (file, prop, value) values (?, ?, ?)',
					$file,
					$prop,
					$value
				)) {
					self::$error = DB::error ();
					return false;
				}
			} else {
				// already exists, update
				if (! DB::execute (
					'update #prefix#filemanager_prop set value = ? where file = ? and prop = ?',
					$value,
					$file,
					$prop
				)) {
					self::$error = DB::error ();
					return false;
				}
			}
			return $value;
		}
		if (is_array ($file)) {
			// get as a list
			$qmarks = array_fill (0, count ($file), '?');
			$file[] = $prop;
			return DB::pairs (
				'select file, value from #prefix#filemanager_prop where file in(' . join (', ', $qmarks) . ') and prop = ?',
				$file
			);
		}
		// get a single value
		return DB::shift (
			'select value from #prefix#filemanager_prop where file = ? and prop = ?',
			$file,
			$prop
		);
	}

	/**
	 * Rename the properties for a file that has been renamed.
	 */
	public static function prop_rename ($file, $new_name, $folder = false) {
		if ($folder) {
			if (! DB::execute (
				'update #prefix#filemanager_prop set file = replace(file, ?, ?) where file like ?',
				$file . '/',
				$new_name . '/',
				$file . '/%'
			)) {
				self::$error = DB::error ();
				return false;
			}
			return true;
		}
		if (! DB::execute (
			'update #prefix#filemanager_prop set file = ? where file = ?',
			$new_name,
			$file
		)) {
			self::$error = DB::error ();
			return false;
		}
		return true;
	}

	/**
	 * Delete the properties for a file that has been deleted.
	 */
	public static function prop_delete ($file) {
		if (! DB::execute (
			'delete from #prefix#filemanager_prop where file = ?',
			$file
		)) {
			self::$error = DB::error ();
			return false;
		}
		return true;
	}
}
