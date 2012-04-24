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
 * Provides a Memcache-compatible wrapper for the PHP APC extension.
 *
 * This allows you to use APC as a drop-in replacement for Memcache as a cache
 * store in Elefant.
 *
 * To enable, edit the `conf/config.php` file and find the `[Cache]` section.
 * Change the `backend` value to `apc`.
 */
class MemcacheAPC {
	/**
	 * Emulates `MemcacheExt::cache`.
	 */
	public function cache ($key, $timeout, $function) {
		if (($val = $this->get ($key)) === false) {
			$val = $function ();
			$this->set ($key, $val, 0, $timeout);
		}
		return $val;
	}

	/**
	 * Emulates `Memcache::get`.
	 */
	public function get ($key) {
		$value = apc_fetch ($key);
		if (preg_match ('/^(a|O):[0-9]+:/', $value)) {
			return unserialize ($value);
		}
		return $value;
	}

	/**
	 * Emulates `Memcache::add`.
	 */
	public function add ($key, $value, $flag = 0, $expire = false) {
		if (is_array ($value) || is_object ($value)) {
			$value = serialize ($value);
		}
		if (apc_exists ($key)) {
			return false;
		}
		apc_store ($key, $value, $expire);
	}

	/**
	 * Emulates `Memcache::set`.
	 */
	public function set ($key, $value, $flag = 0, $expire = false) {
		if (is_array ($value) || is_object ($value)) {
			$value = serialize ($value);
		}
		if ($expire) {
			return apc_store ($key, $value, $expire);
		}
		return apc_store ($key, $value);
	}

	/**
	 * Emulates `Memcache::replace`.
	 */
	public function replace ($key, $value, $flag = 0, $expire = false) {
		if (is_array ($value) || is_object ($value)) {
			$value = serialize ($value);
		}
		if (! apc_exists ($key)) {
			return false;
		}
		return apc_store ($key, $value, $expire);
	}

	/**
	 * Emulates `Memcache::delete`.
	 */
	public function delete ($key) {
		return apc_delete ($key);
	}

	/**
	 * Emulates `Memcache::increment`.
	 */
	public function increment ($key, $value = 1) {
		return apc_inc ($key, $value);
	}

	/**
	 * Emulates `Memcache::decrement`.
	 */
	public function decrement ($key, $value = 1) {
		return apc_dec ($key, $value);
	}

	/**
	 * Emulates `Memcache::flush`.
	 */
	public function flush () {
		return apc_clear_cache ('user');
	}
}

?>