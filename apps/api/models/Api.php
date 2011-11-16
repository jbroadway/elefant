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
 * Authentication based on tokens associated with user accounts.
 * To create new tokens, use:
 *
 *   list ($token, $key) = Api::create_token ($user_id);
 *
 * To authenticate, use:
 *
 *   if (! Api::require_auth ()) {
 *     // unauthorized
 *   }
 */
class Api extends Model {
	/**
	 * The auth token for the request.
	 */
	public $key = 'token';

	/**
	 * Verifies a token/key combo against the database.
	 * Used by require_auth().
	 */
	public static function verifier ($token, $key) {
		$u = db_single (
			'select * from api where token = ? and api_key = ?',
			$token,
			$key
		);
		if ($u) {
			global $user;
			$user = new User ($u->user_id);
			return true;
		}
		return false;
	}

	/**
	 * Custom handler for simple_auth(). Used by require_auth().
	 */
	public static function method ($callback) {
		if (isset ($_SERVER['PHP_AUTH_USER']) && isset ($_SERVER['PHP_AUTH_PW'])) {
			return call_user_func ($callback, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
		}
		return false;
	}

	/**
	 * Authorize a request using HTTP basic using their API token and key.
	 */
	public static function require_auth () {
		return simple_auth (array ('Api', 'verifier'), array ('Api', 'method'));
	}

	/**
	 * Creates and returns a new token/api_key combination for the
	 * specified user ID. Returns an array with the two values. Note
	 * that for an existing user ID, this will generate a new pair,
	 * replacing the old values and making them no longer valid for
	 * API access.
	 */
	public static function create_token ($user_id) {
		$a = Api::query ()
			->where ('user_id', $user_id)
			->fetch ();

		if (count ($a) > 0) {
			$a = $a[0];
			$a->token = md5 (uniqid (mt_rand (), 1));
			$a->api_key = md5 (uniqid (mt_rand (), 1));
		} else {
			$a = new Api (array (
				'token' => md5 (uniqid (mt_rand (), 1)),
				'api_key' => md5 (uniqid (mt_rand (), 1)),
				'user_id' => $user_id
			));
		}
		while (! $a->put ()) {
			$a->token = md5 (uniqid (mt_rand (), 1));
		}
		return array ($a->token, $a->api_key);
	}
}

?>