<?php

/**
 * Fields:
 *
 * id
 * email
 * password
 * session_id
 * expires
 * name
 * signed_up
 * updated
 *
 * Basic usage of additional methods:
 *
 *   // Send unauth users to myapp/login view
 *   if (! User::require_login ()) {
 *     $page->template = 'myapp/login';
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
	/**
	 * Generates a random salt and encrypts a password using MD5.
	 */
	function encrypt_pass ($plain) {
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
	function verifier ($user, $pass) {
		$u = db_single (
			'select * from user where email = %s',
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
	function method ($callback) {
		@session_set_cookie_params (time () + 2592000);
		@session_start ();
		if (isset ($_POST['username']) && isset ($_POST['password'])) {
			return call_user_func ($callback, $_POST['username'], $_POST['password']);
		} elseif (isset ($_SESSION['session_id'])) {
			$u = db_single (
				'select * from user where session_id = %s and expires > %s',
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
	function require_login () {
		return simple_auth (array ('User', 'verifier'), array ('User', 'method'));
	}

	/**
	 * Check if a user is valid.
	 */
	function is_valid () {
		global $user;
		if (is_object ($user) && $user->session_id == $_SESSION['session_id']) {
			return true;
		}
		return User::require_login ();
	}

	/**
	 * Log out and optionally redirect to the specified URL.
	 */
	function logout ($redirect_to = false) {
		global $user;
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
}

?>