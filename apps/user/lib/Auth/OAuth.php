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
 *   $this->require_auth (user\Auth\OAuth::init ());
 *
 *   // User has been authorized via OAuth
 * 
 *   // Or to create a token controller
 * 
 *   use user\Auth\OAuth;
 * 
 *   $server = OAuth::init_server ();
 *   $request = OAuth2\Request::createFromGlobals ();
 *   $response = new Oauth2\Response ();
 * 
 *   if (! $server->validateAuthorizeRequest ($request, $response)) {
 *     $response->send ();
 *     exit;
 *   }
 * 
 *   // Create a form to let the user authorize the app
 * 
 *   $authorized = ($_POST['authorized'] === 'yes');
 *   $server->handleAuthorizeRequest ($request, $response, $authorized, User::current ()->id);
 *   $response->send ();
 */
class OAuth {
	
	/**
	 * The user ID of the last user to be verified. Defaults to 0.
	 */
	public static $user_id = 0;

	private static $storage;

	private static $server;

	/**
	 * Initialize the server. Note: Must be done for any page interacting
	 * with OAuth, not just the API endpoints used via `require_auth()`.
	 * For pages that need to interact with the server directly, this
	 * method returns the server object for you to do so.
	 */
	public static function init_server () {
		self::$storage = new DBStorage ();
		self::$server = new Server (self::$storage);
		self::$server->addGrantType (new ClientCredentials (self::$storage));
		self::$server->addGrantType (new AuthorizationCode (self::$storage));
		return self::$server;
	}

	/**
	 * Returns an array with the verifier and request method callbacks
	 * that will be passed to `simple_auth()`. Note: Automatically calls
	 * `init_server()` for you.
	 */
	public static function init () {
		self::init_server ();

		return array (
			array ('user\Auth\OAuth', 'verifier'),
			array ('user\Auth\OAuth', 'method')
		);
	}

	/**
	 * Verifies the authenticity of the provided OAuth resource request.
	 */
	public static function verifier ($request) {
		if (! self::$server->verifyResourceRequest ($request)) {
			return false;
		}

		// Get the user ID of the token owner
		$token = self::$server->getAccessTokenData ($request);
		self::$user_id = $token['user_id'];
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
		// Call the verifier with the resource request
		if (! call_user_func ($callback, Request::createFromGlobals ())) {
			self::$server->getResponse ()->send ();
			exit;
		}

		return true;
	}
}
