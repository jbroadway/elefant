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
 * Authentication based on tokens associated with user accounts. Creates a unique
 * token and a private key and associates it with a user account. The private key
 * can be used to create a hash of request data that in combination with the token
 * can validate the client request.
 *
 * To create new tokens, use:
 *
 *   list ($token, $key) = Api::create_token ($user_id);
 *
 * To authenticate, use the `user\Auth\HMAC` authentication scheme:
 *
 *   $this->require_auth (user\Auth\HMAC::init ($this, $cache));
 */
class Api extends Model {
	/**
	 * The database table name.
	 */
	public $table = '#prefix#api';

	/**
	 * The auth token for the request.
	 */
	public $key = 'token';

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
