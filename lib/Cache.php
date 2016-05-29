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
 * This is a basic class that immitates Memcache via the filesystem
 * so handlers can use "poor man's caching" even if Memcache ins't
 * available.
 *
 * Also provides an `init()` method that initializes the correct cache
 * for the current request (Memcache, Redis, or itself).
 */
class Cache {
	/**
	 * Directory to use for cache.
	 */
	public $dir = 'cache/datastore';

	/**
	 * Constructor method creates the directory if it's missing.
	 */
	public function __construct ($dir = 'cache/datastore') {
		$this->dir = $dir;

		if (! file_exists ($this->dir)) {
			if (! is_writeable (dirname ($dir))) {
				die ('Cache folder must be writeable to continue. Please check the <a href="https://www.elefantcms.com/docs/2.0/getting-started/file-permissions" target="_blank">installation instructions</a> and try again.');
			}
			mkdir ($this->dir);
			chmod ($this->dir, 0777);
		}
	}

	/**
	 * Initialize the correct cache based on the global configuration
	 * and return the cache object (lib/MemcacheExt, lib/MemcacheAPC,
	 * lib/MemcacheXCache, lib/MemcacheRedis, or lib/Cache).
	 */
	public static function init ($conf) {
		$server = isset ($conf['server']) ? $conf['server'] : false;
		$dir = isset ($conf['location']) ? $conf['location'] : 'cache/datastore';
		$backend = isset ($conf['backend']) ? $conf['backend'] : 'memcache';

		if ($backend === 'apc' && (extension_loaded ('apc') || extension_loaded ('apcu'))) {
			return new MemcacheAPC ();
		} elseif ($backend === 'xcache' && extension_loaded ('xcache')) {
			return new MemcacheXCache ();
		} elseif ($server) {
			// Determine the backend
			if ($backend === 'redis' && extension_loaded ('redis')) {
				$cache = new MemcacheRedis ();
			} elseif (extension_loaded ('memcache')) {
				$cache = new MemcacheExt ();
			} else {
				return new Cache ($dir);
			}

			// Connect to cache server(s)
			foreach ($server as $s) {
				list ($serv, $port) = explode (':', $s);
				if (strpos ($port, ',') !== false) {
					// There's an appended password
					list ($port, $password) = explode (',', $port);
					$cache->addServer ($serv, $port, $password);
				} else {
					// No auth
					$cache->addServer ($serv, $port);
				}
			}
			return $cache;
		}

		// No server, use fs
		return new Cache ($dir);
	}

	/**
	 * Create a timeout file to store the timeout of the cached data.
	 * Uses a similarly named dot-file to the main file that contains
	 * only the timeout value.
	 */
	private function _set_timeout ($key, $timeout) {
		if (file_put_contents ($this->dir . '/.' . md5 ($key), $timeout)) {
			chmod ($this->dir . '/.' . md5 ($key), 0666);
			return true;
		}
		return false;
	}

	/**
	 * Checks whether a key's timeout has expired. If it has, it
	 * also deletes the timeout dot-file.
	 */
	private function _has_timed_out ($key) {
		$timeout_file = $this->dir . '/.' . md5 ($key);
		if (! file_exists ($timeout_file)) {
			return false;
		}
		$timeout = file_get_contents ($timeout_file);
		$mtime = filemtime ($timeout_file);
		if ($mtime < time () - $timeout) {
			unlink ($timeout_file);
			return true;
		}
		return false;
	}

	/**
	 * Emulates `MemcacheExt::cache`.
	 */
	public function cache ($key, $timeout, $function) {
		if (($val = $this->get ($key)) === false) {
			if (is_callable ($function)) {
				$val = call_user_func ($function);
			} else {
				$val = $function;
			}
			$this->set ($key, $val, 0, $timeout);
		}
		return $val;
	}

	/**
	 * Emulates `Memcache::get`.
	 */
	public function get ($key) {
		if (file_exists ($this->dir . '/' . md5 ($key))) {
			if ($this->_has_timed_out ($key)) {
				return false;
			}
			$val = file_get_contents ($this->dir . '/' . md5 ($key));
			if (preg_match ('/^(a|O):[0-9]+:/', $val)) {
				return unserialize ($val);
			}
			return $val;
		}
		return false;
	}

	/**
	 * Emulates `Memcache::add`.
	 */
	public function add ($key, $val, $flags = 0, $timeout = false) {
		if (is_array ($val) || is_object ($val)) {
			$val = serialize ($val);
		}
		if (file_exists ($this->dir . '/' . md5 ($key))) {
			return false;
		}
		if (! file_put_contents ($this->dir . '/' . md5 ($key), $val)) {
			return false;
		}
		chmod ($this->dir . '/' . md5 ($key), 0666);
		if ($timeout) {
			$this->_set_timeout ($key, $timeout);
		}
		return true;
	}

	/**
	 * Emulates `Memcache::replace`.
	 */
	public function replace ($key, $val, $flags = 0, $timeout = false) {
		if (is_array ($val) || is_object ($val)) {
			$val = serialize ($val);
		}
		if (! file_put_contents ($this->dir . '/' . md5 ($key), $val)) {
			return false;
		}
		chmod ($this->dir . '/' . md5 ($key), 0666);
		if ($timeout) {
			$this->_set_timeout ($key, $timeout);
		}
		return true;
	}

	/**
	 * Emulates `Memcache::set`.
	 */
	public function set ($key, $val, $flags = 0, $timeout = false) {
		if (is_array ($val) || is_object ($val)) {
			$val = serialize ($val);
		}
		if (! file_put_contents ($this->dir . '/' . md5 ($key), $val)) {
			return false;
		}
		chmod ($this->dir . '/' . md5 ($key), 0666);
		if ($timeout) {
			$this->_set_timeout ($key, $timeout);
		}
		return true;
	}

	/**
	 * Emulates `Memcache::increment`.
	 */
	public function increment ($key, $value = 1) {
		if (file_exists ($this->dir . '/' . md5 ($key))) {
			$val = file_get_contents ($this->dir . '/' . md5 ($key));
		} else {
			$val = 0;
		}
		$val += $value;
		if (! file_put_contents ($this->dir . '/' . md5 ($key), $val)) {
			return false;
		}
		chmod ($this->dir . '/' . md5 ($key), 0666);
		return $val;
	}

	/**
	 * Emulates `Memcache::decrement`.
	 */
	public function decrement ($key, $value = 1) {
		if (file_exists ($this->dir . '/' . md5 ($key))) {
			$val = file_get_contents ($this->dir . '/' . md5 ($key));
		} else {
			$val = 0;
		}
		$val -= $value;
		if (! file_put_contents ($this->dir . '/' . md5 ($key), $val)) {
			return false;
		}
		chmod ($this->dir . '/' . md5 ($key), 0666);
		return $val;
	}

	/**
	 * Emulates `Memcache::flush`.
	 */
	public function flush () {
		$files = glob ($this->dir . '/{,.}*', GLOB_BRACE);
		foreach ($files as $file) {
			if (preg_match ('/\/\.+$/', $file)) {
				// Skip . and ..
				continue;
			}
			unlink ($file);
		}
		return true;
	}

	/**
	 * Emulates `Memcache::delete`.
	 */
	public function delete ($key) {
		$file = $this->dir . '/' . md5 ($key);
		if (file_exists ($file)) {
			return unlink ($this->dir . '/' . md5 ($key));
		}
		return true;
	}
}
