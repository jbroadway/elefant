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
 * the first call to any validation method will initialize the `$user`
 * property to contain the static User object.
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
 *     <?php
 *     
 *     // Send unauth users to myapp/login view
 *     if (! User::require_login ()) {
 *         $page->title = i18n_get ('Members');
 *         echo $this->run ('user/login');
 *         return;
 *     }
 *     
 *     // Check if a user is valid at any point
 *     if (! User::is_valid ()) {
 *         // Not allowed
 *     }
 *     
 *     // Check the user's type
 *     if (User::is ('member')) {
 *         // Access granted
 *     }
 *     
 *     // Get the name value
 *     $name = User::val ('name');
 *     
 *     // Get the actual user object
 *     info (User::$user);
 *     
 *     // Update and save a user's name
 *     User::val ('name', 'Bob Diggity');
 *     User::save ();
 *     
 *     // Encrypt a password
 *     $encrypted = User::encrypt_pass ($password);
 *     
 *     // Log out and send them home
 *     User::logout ('/');
 *     
 *     ?>
 */
class User extends ExtendedModel {
	/**
	 * Tell the ExtendedModel which field should contain the extended properties.
	 */
	public $_extended_field = 'userdata';

	/**
	 * This is the static User object for the current user.
	 */
	public static $user = false;

	/**
	 * Access control list for `access()` method.
	 */
	public static $acl = false;

	/**
	 * Generates a random salt and encrypts a password using MD5.
	 */
	public static function encrypt_pass ($plain) {
		$base = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$salt = '$2a$07$';
		for ($i = 0; $i < 22; $i++) {
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
	public static function verifier ($user, $pass) {
		// If it's been called before for this user, return cached result
		static $called = array ();
		if (isset ($called[$user])) {
			return $called[$user];
		}

		$u = DB::single (
			'select * from `user` where email = ?',
			$user
		);

		// Check if they've exceeded their login attempt limit
		global $memcache, $controller;
		$appconf = parse_ini_file ('apps/user/conf/config.php', true);
		$attempts = $memcache->get ('_user_login_attempts_' . session_id ());
		if (! $attempts) {
			$attempts = 0;
		}
		if ($attempts > $appconf['User']['login_attempt_limit']) {
			$called[$user] = false;
			$controller->redirect ('/user/too-many-attempts');
		}

		if ($u && crypt ($pass, $u->password) == $u->password) {
			$class = get_called_class ();
			self::$user = new $class ((array) $u, false);
			self::$user->session_id = md5 (uniqid (mt_rand (), 1));
			self::$user->expires = gmdate ('Y-m-d H:i:s', time () + 2592000); // 1 month
			$try = 0;
			while (! self::$user->put ()) {
				self::$user->session_id = md5 (uniqid (mt_rand (), 1));
				$try++;
				if ($try == 5) {
					$called[$user] = false;
					return false;
				}
			}
			$_SESSION['session_id'] = self::$user->session_id;

			// Save the user agent so we can verify it against future sessions,
			// and remove the login attempts cache item
			$memcache->add ('_user_session_agent_' . $_SESSION['session_id'], $_SERVER['HTTP_USER_AGENT'], 0, time () + 2592000);
			$memcache->delete ('_user_login_attempts_' . session_id ());

			$called[$user] = true;
			return true;
		}

		// Increment the number of attempts they've made
		$attempts++;
		if (! $memcache->add ('_user_login_attempts_' . session_id (), $attempts, 0, $appconf['User']['block_attempts_for'])) {
			$memcache->replace ('_user_login_attempts_' . session_id (), $attempts, 0, $appconf['User']['block_attempts_for']);
		}

		$called[$user] = false;
		return false;
	}

	/**
	 * A custom handler for simple_auth(). Note: Calls session_start()
	 * for you, and creates the global $user object if a session is
	 * valid, since we have the data already.
	 */
	public static function method ($callback) {
		if (! isset ($_SESSION)) {
			@session_set_cookie_params (time () + 2592000);
			@session_start ();
		}
		if (isset ($_POST['username']) && isset ($_POST['password'])) {
			return call_user_func ($callback, $_POST['username'], $_POST['password']);
		} elseif (isset ($_SESSION['session_id'])) {
			$u = DB::single (
				'select * from `user` where session_id = ? and expires > ?',
				$_SESSION['session_id'],
				gmdate ('Y-m-d H:i:s')
			);
			if ($u) {
				// Verify user agent as a last step (make hijacking harder)
				global $memcache;
				$ua = $memcache->get ('_user_session_agent_' . $_SESSION['session_id']);
				if ($ua && $ua !== $_SERVER['HTTP_USER_AGENT']) {
					return false;
				}

				$class = get_called_class ();
				self::$user = new $class ((array) $u, false);
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
	public static function require_login () {
		$class = get_called_class ();
		return simple_auth (array ($class, 'verifier'), array ($class, 'method'));
	}

	/**
	 * Simplifies authorization for admins down to:
	 *
	 *   if (! User::require_admin ()) {
	 *     // unauthorized
	 *   }
	 */
	public static function require_admin () {
		if (is_object (self::$user)) {
			if (self::$user->session_id == $_SESSION['session_id']) {
				if (self::$user->type == 'admin') {
					return true;
				}
				return false;
			}
		} else {
			$class = get_called_class ();
			$res = simple_auth (array ($class, 'verifier'), array ($class, 'method'));
			if ($res && self::$user->type == 'admin') {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if a user is valid.
	 */
	public static function is_valid () {
		if (is_object (self::$user) && self::$user->session_id == $_SESSION['session_id']) {
			return true;
		}
		return self::require_login ();
	}

	/**
	 * Check if a user is of a certain type.
	 */
	public static function is ($type) {
		return (self::$user->type === $type);
	}

	/**
	 * Loads the access control list for the `access()` method.
	 */
	private static function load_acl () {
		if (self::$acl === false) {
			$appconf = parse_ini_file ('apps/user/conf/config.php', true);
			self::$acl = $appconf['Access'];
			// make the default access levels translatable
			i18n_get ('Public'); i18n_get ('Member'); i18n_get ('Private');
		}
	}

	/**
	 * Verify a user can access the specified access level based
	 * on their user type.
	 */
	public static function access ($access) {
		self::load_acl ();

		if (! isset (self::$acl[$access])) {
			return false;
		}

		if (self::$acl[$access] === 'all') {
			return true;
		}

		if (self::$acl[$access] === 'login' && self::is_valid ()) {
			return true;
		}

		if (self::$acl[$access] === 'admin' && self::is ('admin')) {
			return true;
		}

		if (strpos (self::$acl[$access], 'type:') === 0) {
			$type = str_replace ('type:', '', self::$acl[$access]);
		} else {
			$type = self::$acl[$access];
		}

		if (self::is ($type)) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the list of access levels.
	 */
	public static function access_list () {
		self::load_acl ();
		return array_keys (self::$acl);
	}

	/**
	 * Get or set a specific field's value.
	 */
	public static function val ($key, $val = null) {
		if ($val !== null) {
			self::$user->{$key} = $val;
		}
		return self::$user->{$key};
	}

	/**
	 * Save the user's data to the database.
	 */
	public static function save () {
		return self::$user->put ();
	}

	/**
	 * Log out and optionally redirect to the specified URL.
	 */
	public static function logout ($redirect_to = false) {
		if (self::$user === false) {
			self::require_login ();
		}
		if (! empty (self::$user->session_id)) {
			self::$user->expires = gmdate ('Y-m-d H:i:s', time () - 100000);
			self::$user->put ();
		}
		$_SESSION['session_id'] = null;
		if ($redirect_to) {
			global $controller;
			$controller->redirect ($redirect_to);
		}
	}
}

?>