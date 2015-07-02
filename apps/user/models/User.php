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
 * Note that this class extends [[ExtendedModel]], so all of the [[ExtendedModel]]
 * and [[Model]] methods are available for querying the user list, and for user
 * management, as well.
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
 *         $page->title = __ ('Members');
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
	 * The database table name.
	 */
	public $table = '#prefix#user';

	/**
	 * Tell the ExtendedModel which field should contain the extended properties.
	 */
	public $_extended_field = 'userdata';

	/**
	 * This is the static User object for the current user.
	 */
	public static $user = FALSE;
	
	/**
	 * Session object for storing session IDs and expiry times,
	 * when `multi_login` is enabled.
	 */
	public static $session = null;

	/**
	 * Acl object for `require_acl()` method. Get and set via `User::acl()`.
	 */
	public static $acl = null;

	/**
	 * Get all social links for the current user.
	 * Alias of `user\Link::for_user ($user_id)`
	 */
	public function links () {
		return user\Link::for_user ($this->id);
	}

	/**
	 * Get all notes for the current user.
	 * Alias of `user\Note::for_user ($user_id)`
	 */
	public function notes () {
		return user\Note::for_user ($this->id);
	}

	/**
	 * Generates a random salt and encrypts a password using Blowfish.
	 */
	public static function encrypt_pass ($plain) {
		if (defined ('PASSWORD_DEFAULT')) {
			return password_hash ($plain, PASSWORD_BCRYPT);
		}
		return crypt ($plain, '$2a$10$' . substr (sha1 (mt_rand ()), 0, 22) . '$');
	}

	/**
	 * Takes a length and returns a random string of characters of that
	 * length for use in passwords. String may contain any number, lower
	 * or uppercase letters, or common symbols.
	 */
	public static function generate_pass ($length = 8) {
		$list = '123467890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_-+=~:;|<>[]{}?"\'';
		$pass = '';
		while (strlen ($pass) < $length) {
			$pass .= substr ($list, mt_rand (0, strlen ($list)), 1);
		}
		return $pass;
	}

	/**
	 * Initializes the PHP session with the right settings
	 * and save handler.
	 */
	public static function init_session ($name = false, $duration = false, $path = '/', $domain = false, $secure = false, $httponly = true) {
		if (! isset ($_SESSION)) {
			$name = $name ? $name : conf ('General', 'session_name');
			$duration = $duration ? $duration : conf ('General', 'session_duration');
			$domain = $domain ? $domain : conf ('General', 'session_domain');
			$handler_class = conf ('General', 'session_save_handler');

			if ($domain === 'full') {
				$domain = $_SERVER['HTTP_HOST'];
			} elseif ($domain === 'top') {
				$parts = explode ('.', $_SERVER['HTTP_HOST']);
				$tld = array_pop ($parts);
				$domain = '.' . array_pop ($parts) . '.' . $tld;
			}
			ini_set ('session.gc_maxlifetime', (int) $duration);
			@session_set_cookie_params ($duration, $path, $domain, $secure, $httponly);
			@session_name ($name);

			if ($handler_class) {
				if (strpos ($handler_class, ':') !== false) {
					list ($handler, $save_path) = explode (':', $handler_class, 2);
					ini_set ('session.save_handler', $handler);
					ini_set ('session.save_path', $save_path);
				} elseif (class_exists ($handler_class)) {
					$handler = new $handler_class (conf ('General', 'site_key'));
					@session_set_save_handler ($handler, true);
				}
			}

			@session_start ();

			if (isset ($_COOKIE[$name])) {
				setcookie ($name, $_COOKIE[$name], time() + $duration, $path, $domain, $secure, $httponly);
			}
		}
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
			'select * from `#prefix#user` where email = ?',
			$user
		);

		// Check if they've exceeded their login attempt limit
		global $controller;
		$cache = $controller->cache ();
		$attempts = $cache->get ('_user_login_attempts_' . session_id ());
		if (! $attempts) {
			$attempts = 0;
		}
		if ($attempts > Appconf::user ('User', 'login_attempt_limit')) {
			$called[$user] = FALSE;
			$controller->redirect ('/user/too-many-attempts');
		}

		if ($u && crypt ($pass, $u->password) == $u->password) {
			$class = get_called_class ();
			self::$user = new $class ((array) $u, FALSE);
			if (Appconf::user ('User', 'multi_login')) {
				self::$session = user\Session::create ($u->id);
				if (self::$session === false) {
					$called[$user] = FALSE;
					return false;
				}
				self::$user->session_id = self::$session->session_id;
				self::$user->expires = self::$session->expires;
				$_SESSION['session_id'] = self::$user->session_id;
			} else {
				self::$user->session_id = md5 (uniqid (mt_rand (), 1));
				self::$user->expires = gmdate ('Y-m-d H:i:s', time () + 2592000); // 1 month
				$try = 0;
				while (! self::$user->put ()) {
					self::$user->session_id = md5 (uniqid (mt_rand (), 1));
					$try++;
					if ($try == 5) {
						$called[$user] = FALSE;
						return FALSE;
					}
				}
				$_SESSION['session_id'] = self::$user->session_id;
			}

			// Save the user agent so we can verify it against future sessions,
			// and remove the login attempts cache item
			$cache->add ('_user_session_agent_' . $_SESSION['session_id'], $_SERVER['HTTP_USER_AGENT'], 0, time () + 2592000);
			$cache->delete ('_user_login_attempts_' . session_id ());

			$called[$user] = TRUE;
			return TRUE;
		}

		// Increment the number of attempts they've made
		$attempts++;
		if (! $cache->add ('_user_login_attempts_' . session_id (), $attempts, 0, Appconf::user ('User', 'block_attempts_for'))) {
			$cache->replace ('_user_login_attempts_' . session_id (), $attempts, 0, Appconf::user ('User', 'block_attempts_for'));
		}

		$called[$user] = FALSE;
		return FALSE;
	}

	/**
	 * A custom handler for `simple_auth()`. Note: Calls `session_start()`
	 * for you, and creates the global `$user` object if a session is
	 * valid, since we have the data already.
	 */
	public static function method ($callback) {
		if (isset ($_POST['username']) && isset ($_POST['password'])) {
			self::init_session ();
			return call_user_func ($callback, $_POST['username'], $_POST['password']);

		} else {
			$name = conf ('General', 'session_name');
			if (isset ($_COOKIE[$name]) && ! isset ($_SESSION)) {
				self::init_session ();
			}

			if (isset ($_SESSION['session_id'])) {
				if (Appconf::user ('User', 'multi_login')) {
					$u = \user\Session::fetch_user ($_SESSION['session_id']);
					if (is_object ($u)) {
						$u->session_id = $_SESSION['session_id'];
					}
				} else {
					$u = DB::single (
						'select * from `#prefix#user` where session_id = ? and expires > ?',
						$_SESSION['session_id'],
						gmdate ('Y-m-d H:i:s')
					);
				}
				if (is_object ($u)) {
					// Verify user agent as a last step (make hijacking harder)
					global $cache;
					$ua = $cache->get ('_user_session_agent_' . $_SESSION['session_id']);
					if ($ua && $ua !== $_SERVER['HTTP_USER_AGENT']) {
						return FALSE;
					}

					$class = get_called_class ();
					self::$user = new $class ((array) $u, FALSE);
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	/**
	 * Simplifies authorization down to:
	 *
	 *     <?php
	 *
	 *     if (! User::require_login ()) {
	 *         // unauthorized
	 *     }
	 *
	 *     ?>
	 */
	public static function require_login () {
		$class = get_called_class ();
		return simple_auth (array ($class, 'verifier'), array ($class, 'method'));
	}

	/**
	 * Alias of `require_acl('admin')`. Simplifies authorization
	 * for general admin access down to:
	 *
	 *     <?php
	 *
	 *     if (! User::require_admin ()) {
	 *         // unauthorized
	 *     }
	 *
	 *     ?>
	 */
	public static function require_admin () {
		return self::require_acl ('admin');
	}

	/**
	 * Determine whether the current user is allowed to access
	 * a given resource.
	 */
	public static function require_acl ($resource) {
		if (! User::is_valid ()) {
			return false;
		}
		$acl = self::acl ();
		$resources = func_get_args ();
		foreach ($resources as $resource) {
			if (! $acl->allowed ($resource, self::$user)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Check if a user is valid.
	 */
	public static function is_valid () {
		if (is_object (self::$user) && self::$user->session_id == $_SESSION['session_id']) {
			return TRUE;
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
	 * Fetch or set the currently active user.
	 */
	public static function current (User $current = null) {
		if ($current !== null) {
			self::$user = $current;
		}
		return self::$user;
	}

	/**
	 * Alias of `require_acl('content/' . $access)`, prepending the
	 * `content/` string to the resource name before comparing it.
	 * Where `User::require_acl('resource')` is good for validating
	 * access to any resource type, `User::access('member')` is used
	 * for content access levels.
	 *
	 * Can also be called via `User::access()` and it will return an
	 * array of the access values which the current user may access,
	 * for example:
	 *
	 *     array ('public' => 'Public', 'member' => 'Member')
	 */
	public static function access ($access = null) {
		if ($access !== null) {
			return self::require_acl ('content/' . $access);
		}
		
		$access = array ();
		$list = self::access_list ();
		foreach ($list as $k => $v) {
			if (User::access ($k)) {
				$access[$k] = $v;
			}
		}
		return $access;
	}

	/**
	 * Returns the list of access levels for content. This is a list
	 * of resources that begin with `content/` e.g., `content/private`,
	 * with keys as the resource and values as a display name for that
	 * resource:
	 *
	 *     array (
	 *         'public'  => 'Public',
	 *         'member'  => 'Member',
	 *         'private' => 'Private'
	 *     )
	 *
	 * Note: Public is hard-coded, since there's no need to verify
	 * access to public resources, but you still need an access level
	 * to specify it.
	 */
	public static function access_list () {
		$acl = self::acl ();
		$resources = $acl->resources ();
		$access = array ('public' => __ ('Public'));
		foreach ($resources as $key => $value) {
			if (strpos ($key, 'content/') === 0) {
				$resource = str_replace ('content/', '', $key);
				$access[$resource] = __ (ucfirst ($resource));
			}
		}
		return $access;
	}

	/**
	 * Get or set the Acl object.
	 */
	public static function acl ($acl = null) {
		if ($acl !== null) {
			self::$acl = $acl;
		}

		if (self::$acl === null) {
			self::$acl = new Acl (conf ('Paths', 'access_control_list'));
		}

		return self::$acl;
	}
	
	/**
	 * Fetch the roles that an admin user is allowed to assign.
	 */
	public static function allowed_roles () {
		$allowed_roles = array ();
		$roles = array_keys (self::acl ()->rules);
		foreach ($roles as $role) {
			if (self::acl ()->allowed ('user/allowed_roles/' . $role)) {
				$allowed_roles[] = $role;
			}
		}
		return $allowed_roles;
	}

	/**
	 * Get or set a specific field's value.
	 */
	public static function val ($key, $val = NULL) {
		if ($val !== NULL) {
			self::$user->{$key} = $val;
		}
		if (! is_object (self::$user)) {
			return null;
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
	public static function logout ($redirect_to = FALSE, $path = '/', $domain = false, $secure = false, $httponly = true) {
		if (self::$user === FALSE) {
			self::require_login ();
		}
		if (Appconf::user ('User', 'multi_login')) {
			user\Session::clear ($_SESSION['session_id']);
			user\Session::clear_expired ();
		} elseif (! empty (self::$user->session_id)) {
			self::$user->expires = gmdate ('Y-m-d H:i:s', time () - 100000);
			self::$user->put ();
		}
		$_SESSION['session_id'] = NULL;

		$name = conf ('General', 'session_name');
		if (isset ($_COOKIE[$name])) {
			$domain = $domain ? $domain : conf ('General', 'session_domain');

			if ($domain === 'full') {
				$domain = $_SERVER['HTTP_HOST'];
			} elseif ($domain === 'top') {
				$parts = explode ('.', $_SERVER['HTTP_HOST']);
				$tld = array_pop ($parts);
				$domain = '.' . array_pop ($parts) . '.' . $tld;
			}

			setcookie ($name, $_COOKIE[$name], time() - 100000, $path, $domain, $secure, $httponly);
		}

		if ($redirect_to) {
			global $controller;
			$controller->redirect ($redirect_to);
		}
	}
}
