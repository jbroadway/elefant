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
 */
class Cache {
	/**
	 * Directory to use for cache.
	 */
	public $dir = 'cache/datastore';

	/**
	 * Constructor method creates the directory if it's missing.
	 */
	public function __construct () {
		if (! file_exists ($this->dir)) {
			mkdir ($this->dir);
			chmod ($this->dir, 0777);
		}
	}

	/**
	 * Emulates `Memcache::cache`.
	 */
	public function cache ($key, $timeout, $function) {
		if (($val = $this->get ($key)) === false) {
			$val = $function ();
			$this->set ($key, $val, $timeout);
		}
		return $val;
	}

	/**
	 * Emulates `Memcache::get`.
	 */
	public function get ($key) {
		if (file_exists ($this->dir . '/' . md5 ($key))) {
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
	public function add ($key, $val) {
		if (is_array ($val) || is_object ($val)) {
			$val = serialize ($val);
		}
		if (file_exists ($this->dir . '/' . md5 ($key))) {
			return false;
		}
		if (! file_put_contents ($this->dir . '/' . md5 ($key), $val)) {
			return false;
		}
		chmod ($this->dir . '/' . md5 ($key), 0777);
		return true;
	}

	/**
	 * Emulates `Memcache::replace`.
	 */
	public function replace ($key, $val) {
		if (is_array ($val) || is_object ($val)) {
			$val = serialize ($val);
		}
		if (! file_put_contents ($this->dir . '/' . md5 ($key), $val)) {
			return false;
		}
		chmod ($this->dir . '/' . md5 ($key), 0777);
		return true;
	}

	/**
	 * Emulates `Memcache::set`.
	 */
	public function set ($key, $val, $timeout = false) {
		if (is_array ($val) || is_object ($val)) {
			$val = serialize ($val);
		}
		if (! file_put_contents ($this->dir . '/' . md5 ($key), $val)) {
			return false;
		}
		chmod ($this->dir . '/' . md5 ($key), 0777);
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
		chmod ($this->dir . '/' . md5 ($key), 0777);
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
		chmod ($this->dir . '/' . md5 ($key), 0777);
		return $val;
	}

	/**
	 * Emulates `Memcache::flush`.
	 */
	public function flush () {
		$files = glob ($this->dir . '/*');
		foreach ($files as $file) {
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

?>