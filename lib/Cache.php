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
 * This is an empty class that immitates Memcache so a global
 * object can exist and hard-coded caching will work even when
 * Memcache isn't installed.
 */
class Cache {
	/**
	 * Internal cache.
	 */
	public $memory = array ();

	/**
	 * Constructor method.
	 */
	public function __construct () {
		// Nothing to do
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
		if (isset ($this->memory[$key])) {
			return $this->memory[$key];
		}
		return false;
	}

	/**
	 * Emulates `Memcache::add`.
	 */
	public function add ($key, $val) {
		$this->memory[$key] = $val;
		return true;
	}

	/**
	 * Emulates `Memcache::replace`.
	 */
	public function replace ($key, $val) {
		$this->memory[$key] = $val;
		return true;
	}

	/**
	 * Emulates `Memcache::set`.
	 */
	public function set ($key, $val, $timeout = false) {
		$this->memory[$key] = $val;
		return true;
	}

	/**
	 * Emulates `Memcache::increment`.
	 */
	public function increment ($key, $value = 1) {
		$this->memory[$key] += $value;
		return $this->memory[$key];
	}

	/**
	 * Emulates `Memcache::decrement`.
	 */
	public function decrement ($key, $value = 1) {
		$this->memory[$key] -= $value;
		return $this->memory[$key];
	}

	/**
	 * Emulates `Memcache::flush`.
	 */
	public function flush () {
		$this->memory = array ();
		return true;
	}

	/**
	 * Emulates `Memcache::delete`.
	 */
	public function delete ($key) {
		unset ($this->memory[$key]);
		return true;
	}
}

?>