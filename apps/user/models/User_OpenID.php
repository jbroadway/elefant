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
 * Stores OpenID tokens and their associated user IDs.
 *
 * Fields:
 *
 * token
 * user_id
 */
class User_OpenID extends Model {
	/**
	 * The database table name.
	 */
	public $table = '#prefix#user_openid';

	/**
	 * The default token name.
	 */
	public $key = 'token';

	/**
	 * Returns a user ID from a token in a single line, or
	 * false if it's not found.
	 *
	 * Usage:
	 *
	 *     $user_id = User_OpenID::get_user_id ($token);
	 */
	public static function get_user_id ($token) {
		$u = new User_OpenID ($token);
		if (! $u->error) {
			return $u->user_id;
		}
		return false;
	}
}

?>