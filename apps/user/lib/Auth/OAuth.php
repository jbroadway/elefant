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

use OAuth2\Server;
use OAuth2\Request;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use user\Auth\OAuth\Storage\DBStorage;

/**
 * Implements an OAuth 2.0 provider for Controller's `require_auth()`.
 * Useful for secure authentication of APIs.
 *
 * Usage:
 *
 *   <?php
 *
 *   $this->require_auth (user\Auth\OAuth::init (
 *     $this,     // Controller
 *     $cache,    // Memcache
 *     3600       // Timeout
 *   ));
 *
 *   // User has been authorized via OAuth
 *
 *   ?>
 */
class OAuth {
	/**
	 * A copy of the controller object required by `init()`.
	 */
	public static $controller = null;

	/**
	 * A copy of the cache object required by `init()`.
	 */
	public static $cache = null;

	/**
	 * A timeout length for caching private API keys, set vai `init()`.
	 * Defaults to one hour.
	 */
	public static $timeout = 3600;
	
	/**
	 * The user ID of the last user to be verified. Defaults to 0.
	 */
	public static $user_id = 0;

	private static $storage;
	private static $provider;

	/**
	 * Returns an array with the verifier and request method callbacks
	 * that will be passed to `simple_auth()`.
	 */
	public static function init ($controller, $cache, $timeout = 3600) {
		self::$controller = $controller;
		self::$cache = $cache;
		self::$timeout = $timeout;

		self::$storage = new DBStorage ();
		self::$server = new Server (self::$storage);
		self::$server->addGrantType (new ClientCredentials (self::$storage));
		self::$server->addGrantType (new AuthorizationCode (self::$storage));

		return array (
			array ('user\Auth\OAuth', 'verifier'),
			array ('user\Auth\OAuth', 'method')
		);
	}

	/**
	 * Verifies the authenticity of the provided OAuth token.
	 */
	public static function verifier ($token, $nonce, $data) {
		if (true /* token valid */) {
			error_log (sprintf ("OAuth auth failed for token %s", $token));
			return false;
		}

		// They have the private key, save the user
		self::$user_id = $user_id;
		return true;
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
				$data = $method . \conf ('General', 'site_domain') . $_SERVER['REQUEST_URI'];
				break;
			case 'PUT':
			case 'POST':
			case 'DELETE':
			default:
				$data = $method . \conf ('General', 'site_domain') . $_SERVER['REQUEST_URI'] . self::$controller->get_put_data ();
				break;
		}

		// Avoid problems with %20 vs +		
		$data = str_replace (['%20', '%28', '%29'], ['+', '(', ')'], $data);

		// Avoid problems with %2f vs %2F
		$data = strtolower ($data);

		// Call the verifier with the token, hmac hash, and request data
		if (! call_user_func ($callback, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $data)) {
			header ('WWW-Authenticate: Basic realm="API Access"');
			header ('HTTP/1.0 401 Unauthorized');
			exit;
		}

		return TRUE;
	}
}
