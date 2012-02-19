<?php

namespace user\Auth;

/**
 * Implements HTTP Basic authentication for Controller's `require_auth()`.
 *
 * Usage:
 *
 *   <?php
 *   
 *   $this->require_auth (user\Auth\Basic::init ());
 *   
 *   // User has been authorized via HTTP Basic
 *   
 *   ?>
 */
class Basic {
	/**
	 * Returns an array with the verifier and request method callbacks
	 * that will be passed to `simple_auth()`.
	 */
	public static function init () {
		return array (
			array ('User', 'verifier'),
			'simple_auth_basic'
		);
	}
}

?>