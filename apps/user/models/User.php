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
 * This is the default user authentication source for Elefant. Provides the
 * basic `User::require_login()` and `User::require_admin()` methods, as
 * well as `User::is_valid()` and `User::logout()`. If a user is logged in,
 * the first call to any validation method will create a global `$user`
 * object to store the user data.
 *
 * It uses a global singleton so the user data can be available to any part
 * of the application, and it makes sense to do so because there is only one
 * active user account per request. It's also much nicer to type `$user`
 * than `User::$user` throughout your code.
 *
 * Note that this class extends Model, so all of the Model methods are
 * available for querying the user list, and for user management, as well.
 *
 * Fields:
 *
 * - id
 * - email
 * - password
 * - session_id
 * - expires
 * - name
 * - type
 * - signed_up
 * - updated
 * - userdata
 *
 * Basic usage of additional methods:
 *
 *   // Send unauth users to myapp/login view
 *   if (! User::require_login ()) {
 *     $page->title = i18n_get ('Members');
 *     echo $this->run ('user/login');
 *     return;
 *   }
 *
 *   // Check if a user is valid at any point
 *   if (! User::is_valid ()) {
 *     // Not allowed
 *   }
 *
 *   // Encrypt a password
 *   $encrypted = User::encrypt_pass ($password);
 *
 *   // Log out and send them home
 *   User::logout ('/');
 */
class User extends Model {
	var $_userdata = false;

	/**
	 * Generates a random salt and encrypts a password using MD5.
	 */
	static function encrypt_pass ($plain) {
		$base = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$salt = '$1$';
		for ($i = 0; $i < 9; $i++) {
			$salt .= $base[rand (0, 61)];
		}
		return crypt ($plain, $salt . '$');
	}

	/**
	 * Verifies a username/password combo against the database.
	 * Username is matched to the email field. If things check out,
	 * a session_id is generated and initialized in the database
	 * and for the user. Also creates the global $user object
	 * as well, since we have the data (no sense requesting it
	 * twice).
	 */
	static function verifier ($user, $pass) {
		$u = db_single (
			'select * from user where email = ?',
			$user
		);
		if ($u && crypt ($pass, $u->password) == $u->password) {
			global $user;
			$user = new User ((array) $u, false);
			$user->session_id = md5 (uniqid (mt_rand (), 1));
			$user->expires = gmdate ('Y-m-d H:i:s', time () + 2592000); // 1 month
			$try = 0;
			while (! $user->put ()) {
				$user->session_id = md5 (uniqid (mt_rand (), 1));
				$try++;
				if ($try == 5) {
					return false;
				}
			}
			$_SESSION['session_id'] = $user->session_id;
			return true;
		}
		return false;
	}

	/**
	 * A custom handler for simple_auth(). Note: Calls session_start()
	 * for you, and creates the global $user object if a session is
	 * valid, since we have the data already.
	 */
	static function method ($callback) {
		@session_set_cookie_params (time () + 2592000);
		@session_start ();
		if (isset ($_POST['username']) && isset ($_POST['password'])) {
			return call_user_func ($callback, $_POST['username'], $_POST['password']);
		} elseif (isset ($_SESSION['session_id'])) {
			$u = db_single (
				'select * from user where session_id = ? and expires > ?',
				$_SESSION['session_id'],
				gmdate ('Y-m-d H:i:s')
			);
			if ($u) {
				$GLOBALS['user'] = new User ((array) $u, false);
				return true;
			}
		}
		return false;
	}

	/**
	 * Simplifies authorization down to:
	 *
	 *   if (! User::require_login ()) {
	 *     // unauthorized
	 *   }
	 */
	static function require_login () {
		return simple_auth (array ('User', 'verifier'), array ('User', 'method'));
	}

	/**
	 * Simplifies authorization for admins down to:
	 *
	 *   if (! User::require_admin ()) {
	 *     // unauthorized
	 *   }
	 */
	static function require_admin () {
		global $user;
		if (is_object ($user)) {
			if ($user->session_id == $_SESSION['session_id']) {
				if ($user->type == 'admin') {
					return true;
				}
				return false;
			}
		} else {
			$res = simple_auth (array ('User', 'verifier'), array ('User', 'method'));
			if ($res && $user->type == 'admin') {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if a user is valid.
	 */
	static function is_valid () {
		global $user;
		if (is_object ($user) && $user->session_id == $_SESSION['session_id']) {
			return true;
		}
		return User::require_login ();
	}

	/**
	 * Log out and optionally redirect to the specified URL.
	 */
	static function logout ($redirect_to = false) {
		global $user;
		if (! isset ($user)) {
			User::require_login ();
		}
		if (! empty ($user->session_id)) {
			$user->expires = gmdate ('Y-m-d H:i:s', time () - 100000);
			$user->put ();
		}
		$_SESSION['session_id'] = null;
		if ($redirect_to) {
			header ('Location: ' . $redirect_to);
			exit;
		}
	}

	function __get ($key) {
		if ($key == 'userdata') {
			if ($this->_userdata === false) {
				$this->_userdata = (array) json_decode ($this->data['userdata']);
			}
			return $this->_userdata;
		}
		return parent::__get ($key);
	}

	function __set ($key, $val) {
		if ($key == 'userdata') {
			$this->_userdata = $val;
			$this->data[$key] = json_encode ($val);
			return;
		}
		return parent::__set ($key, $val);
	}
}

?>