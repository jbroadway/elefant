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

namespace user\Auth;

/**
 * Implements HMAC authentication for Controller's `require_auth()`.
 * Useful for secure token-based authentication of RESTful APIs.
 *
 * Usage:
 *
 *   <?php
 *
 *   $this->require_auth (user\Auth\HMAC::init (
 *     $this,     // Controller
 *     $cache, // Memcache
 *     3600       // Timeout
 *   ));
 *
 *   // User has been authorized via HMAC
 *   $this->restful (new MyRestfulClass ());
 *
 *   ?>
 */
class HMAC {
	/**
	 * A copy of the controller object required by `init()`.
	 */
	public static $controller = NULL;

	/**
	 * A copy of the cache object required by `init()`.
	 */
	public static $cache = NULL;

	/**
	 * A timeout length for caching private API keys, set vai `init()`.
	 * Defaults to one hour.
	 */
	public static $timeout = 3600;

	/**
	 * Returns an array with the verifier and request method callbacks
	 * that will be passed to `simple_auth()`.
	 */
	public static function init ($controller, $cache, $timeout = 3600) {
		self::$controller = $controller;
		self::$cache = $cache;
		self::$timeout = $timeout;

		return array (
			array ('user\Auth\HMAC', 'verifier'),
			array ('user\Auth\HMAC', 'method')
		);
	}

	/**
	 * Verifies the authenticity of the provided token and HMAC hash combo.
	 * Tokens and secret keys are stored in the `api` table.
	 */
	public static function verifier ($token, $hmac, $data) {
		$api_key = self::$cache->get ('_api_key_' . $token);

		if (! $api_key) {
			// API key not yet cached
			$api = new \Api ($token);
			if ($api->error) {
				return FALSE;
			}
			$api_key = $api->api_key;

			// Cache the API key
			$res = self::$cache->replace ('_api_key_' . $token, $api_key, 0, self::$timeout);
			if ($res === FALSE) {
				self::$cache->set ('_api_key_' . $token, $api_key, 0, self::$timeout);
			}
		}

		// Compare our hash calculation with the one given
		if (hash_hmac ('sha256', $data, $api_key) !== $hmac) {
			return FALSE;
		}

		// They have the private key, create the user
		$user = new \User ($api->user_id);
		return TRUE;
	}

	/**
	 * Collects the data to verify against the HMAC hash. The data includes
	 * the token, HMAC hash, and the combined request data (request method,
	 * URI, and PUT/POST data concatenated). Token is passed as the HTTP
	 * Basic username value. HMAC is passed as the HTTP Basic password
	 * value.
	 */
	public static function method ($callback) {
		// Check if user and pass have been sent first.
		if (! isset ($_SERVER['PHP_AUTH_USER']) || ! isset ($_SERVER['PHP_AUTH_PW'])) {
			header ('WWW-Authenticate: Basic realm="API Access"');
			header ('HTTP/1.0 401 Unauthorized');
			exit;
		}

		// Compile request data for comparison
		$method = self::$controller->request_method ();
		$data = '';
		switch ($method) {
			case 'GET':
				$data = $method . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				break;
			case 'PUT':
				$data = $method . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . self::$controller->get_put_data ();
				break;
			case 'POST':
			case 'DELETE':
			default:
				$data = $method . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . self::$controller->get_raw_post_data ();
				break;
		}

		// Call the verifier with the token, hmac hash, and request data
		if (! call_user_func ($callback, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $data)) {
			header ('WWW-Authenticate: Basic realm="API Access"');
			header ('HTTP/1.0 401 Unauthorized');
			exit;
		}

		return TRUE;
	}
}
