<?php

class FileManager {
	static function verify_folder ($path, $root = false) {
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

	static function verify_file ($path, $root = false) {
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

	static function verify_folder_name ($name) {
		if (! preg_match ('/^[a-zA-Z0-9_-]+$/', $name)) {
			return false;
		}
		return true;
	}

	static function verify_file_name ($name) {
		if (! preg_match ('/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+$/', $name)) {
			return false;
		}
		return true;
	}

	static function fsort ($a, $b) {
		return strcmp ($a['name'], $b['name']);
	}
}

?>