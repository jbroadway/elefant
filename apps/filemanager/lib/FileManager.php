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
 * Provides the JSON API for the admin file manager/browser, as well as functions
 * to verify files and folders.
 */
class FileManager extends Restful {
	/**
	 * The path to the root directory to store files.
	 */
	public $root;

	/**
	 * The web path to the root directory.
	 */
	public $webroot = '/files/';

	/**
	 * The error message if an error occurs in one of the static methods.
	 */
	public static $error;

	/**
	 * Constructor sets $root.
	 */
	public function __construct () {
		$this->root = getcwd () . $this->webroot;
	}

	/**
	 * Handle list directory requests (/filemanager/api/ls).
	 */
	public function get_ls () {
		$file = urldecode (join ('/', func_get_args ()));

		if (! self::verify_folder ($file, $this->root)) {
			return $this->error (i18n_get ('Invalid folder name'));
		}

		$d = dir ($this->root . $file);
		$out = array ('dirs' => array (), 'files' => array ());
		while (false !=  ($entry = $d->read ())) {
			if (preg_match ('/^\./', $entry)) {
				continue;
			} elseif (@is_dir ($this->root . $file . '/' . $entry)) {
				$out['dirs'][] = array (
					'name' => $entry,
					'path' => ltrim ($file . '/' . $entry, '/'),
					'mtime' => I18n::date_time (filemtime ($this->root . $file . '/' . $entry))
				);
			} else {
				$out['files'][] = array (
					'name' => $entry,
					'path' => ltrim ($file . '/' . $entry, '/'),
					'mtime' => I18n::date_time (filemtime ($this->root . $file . '/' . $entry)),
					'fsize' => format_filesize (filesize ($this->root . $file . '/' . $entry))
				);
			}
		}
		$d->close ();
		usort ($out['dirs'], array ('FileManager', 'fsort'));
		usort ($out['files'], array ('FileManager', 'fsort'));
		return $out;
	}

	/**
	 * Handle remove file requests (/filemanager/api/rm).
	 */
	public function get_rm () {
		$file = urldecode (join ('/', func_get_args ()));

		if (self::verify_folder ($file, $this->root)) {
			return $this->error (i18n_get ('Unable to delete folders'));
		} elseif (! self::verify_file ($file, $this->root)) {
			return $this->error (i18n_get ('File not found'));
		} elseif (! unlink ($this->root . $file)) {
			return $this->error (i18n_get ('Unable to delete') . ' ' . $file);
		}
		FileManager::prop_delete ($file);
		return array ('msg' => i18n_get ('File deleted.'), 'data' => $file);
	}

	/**
	 * Handle rename requests (/filemanager/api/mv).
	 */
	public function get_mv () {
		$file = urldecode (join ('/', func_get_args ()));
		
		if (self::verify_folder ($file, $this->root)) {
			if (! self::verify_folder_name ($_GET['rename'])) {
				return $this->error (i18n_get ('Invalid folder name'));
			} else {
				$parts = explode ('/', $file);
				$old = array_pop ($parts);
				$new = preg_replace ('/' . preg_quote ($old) . '$/', $_GET['rename'], $file);
				if (! rename ($this->root . $file, $this->root . $new)) {
					return $this->error (i18n_get ('Unable to rename') . ' ' . $file);
				}
				FileManager::prop_rename ($file, $new, true);
				return array ('msg' => i18n_get ('Folder renamed.'), 'data' => $new);
			}
		} elseif (self::verify_file ($file, $this->root)) {
			if (! self::verify_file_name ($_GET['rename'])) {
				return $this->error (i18n_get ('Invalid file name'));
			} else {
				$parts = explode ('/', $file);
				$old = array_pop ($parts);
				$new = preg_replace ('/' . preg_quote ($old) . '$/', $_GET['rename'], $file);
				if (! rename ($this->root . $file, $this->root . $new)) {
					return $this->error (i18n_get ('Unable to rename') . ' ' . $file);
				}
				FileManager::prop_rename ($file, $new);
				return array ('msg' => i18n_get ('File renamed.'), 'data' => $new);
			}
		}
		return $this->error (i18n_get ('File not found'));
	}

	/**
	 * Handle make directory requests (/filemanager/api/mkdir).
	 */
	public function get_mkdir () {
		$file = urldecode (join ('/', func_get_args ()));
		
		$parts = explode ('/', $file);
		$newdir = array_pop ($parts);
		$path = preg_replace ('/\/?' . preg_quote ($newdir) . '$/', '', $file);
		if (! self::verify_folder ($path, $this->root)) {
			return $this->error (i18n_get ('Invalid location'));
		} elseif (! self::verify_folder_name ($newdir)) {
			return $this->error (i18n_get ('Invalid folder name'));
		} elseif (@is_dir ($this->root . $file)) {
			return $this->error (i18n_get ('Folder already exists') . ' ' . $file);
		} elseif (! mkdir ($this->root . $file)) {
			return $this->error (i18n_get ('Unable to create folder') . ' ' . $file);
		}
		chmod ($this->root . $file, 0777);
		return array ('msg' => i18n_get ('Folder created.'), 'data' => $file);
	}

	/**
	 * Handle property update requests (/filemanager/api/prop).
	 */
	public function get_prop () {
		$file = urldecode (join ('/', func_get_args ()));
		if (! self::verify_file ($file, $this->root)) {
			return $this->error (i18n_get ('Invalid file name'));
		}
		if (! isset ($_GET['prop'])) {
			return $this->error (i18n_get ('Missing property name'));
		}
		if (isset ($_GET['value'])) {
			// update and fetch
			$res = self::prop ($file, $_GET['prop'], $_GET['value']);
		} else {
			// fetch
			$res = self::prop ($file, $_GET['prop']);
		}
		return array (
			'file' => $file,
			'prop' => $_GET['prop'],
			'value' => $res,
			'msg' => i18n_get ('Properties saved.')
		);
	}

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
		return DB::pairs ('select prop, value from filemanager_prop where file = ?', $file);
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
			} elseif (! $res) {
				// doesn't exist yet
				if (! DB::execute (
					'insert into filemanager_prop (file, prop, value) values (?, ?, ?)',
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
					'update filemanager_prop set value = ? where file = ? and prop = ?',
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
			$db = DB::get_connection (1);
			if ($db->getAttribute (PDO::ATTR_DRIVER_NAME) === 'mysql') {
				return DB::pairs (
					'select file, value from filemanager_prop where file in(?) and prop = ?',
					implode(', ', $file),
					$prop
				);
			}
			$sql = 'select file, value from filemanager_prop where (';
			$params = array ();
			$join = '';
			foreach ($file as $f) {
				$sql .= $join . 'file = ?';
				$params[] = $f;
				$join = ' or ';
			}
			$sql .= ') and prop = ?';
			$params[] = $prop;
			return DB::pairs ($sql, $params);
		}
		// get a single value
		return DB::shift (
			'select value from filemanager_prop where file = ? and prop = ?',
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
				'update filemanager_prop set file = replace(file, ?, ?) where file like ?',
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
			'update filemanager_prop set file = ? where file = ?',
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
			'delete from filemanager_prop where file = ?',
			$file
		)) {
			self::$error = DB::error ();
			return false;
		}
		return true;
	}
}

?>
