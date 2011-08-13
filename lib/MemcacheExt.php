<?php

/**
 * Extends Memcache with a method that takes a key, timeout, and a
 * function that will be called to produce the value if it is not
 * found in the cache.
 *
 * Based on Zane Ashby's idea posted here:
 *
 * http://demonastery.org/72/tiny-memcached-wrapper/
 */
class MemcacheExt extends Memcache {
	function cache ($key, $timeout, $function) {
		if (($val = $this->get ($key)) === false) {
			$val = $function ();
			$this->set ($key, $val, 0, $timeout);
		}
		return $val;
	}
}

?>