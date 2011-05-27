<?php

/**
 * This is an empty class that immitates Memcache so a global
 * object can exist and hard-coded caching will work even when
 * Memcache isn't installed.
 */
class Cache {
	var $memory = array ();

	function Cache () {
	}

	function get ($key) {
		if (isset ($this->memory[$key])) {
			return $this->memory[$key];
		}
		return false;
	}

	function add ($key, $val) {
		$this->memory[$key] = $val;
		return true;
	}

	function replace ($key, $val) {
		$this->memory[$key] = $val;
		return true;
	}

	function set ($key, $val, $timeout = false) {
		$this->memory[$key] = $val;
		return true;
	}

	function increment ($key, $value = 1) {
		$this->memory[$key] += $value;
		return $this->memory[$key];
	}

	function decrement ($key, $value = 1) {
		$this->memory[$key] -= $value;
		return $this->memory[$key];
	}

	function flush () {
		$this->memory = array ();
		return true;
	}

	function delete ($key) {
		unset ($this->memory[$key]);
		return true;
	}
}

?>