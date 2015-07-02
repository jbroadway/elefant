<?php

namespace user;

/**
 * Custom form rules for the user app.
 */
class Rules {
	/**
	 * Ensure new role name doesn't already exist.
	 */
	public static function new_role ($role) {
		if (isset (\User::acl ()->rules[$role])) {
			return false;
		}
		return true;
	}
	
	/**
	 * Ensure new email address doesn't already belong to another user.
	 * If no `$user_id` is provided, will use `User::val ('id')` to limit
	 * the email address search by the current user. If `$user_id` is
	 * set to `false`, it will not limit by a user ID.
	 */
	public static function email_in_use ($email, $user_id = null) {
		$user_id = ($user_id === null) ? $user_id : \User::val ('id');

		$q = \User::query ()->where ('email', $email);
		
		if ($user_id !== false) {
			$q->where ('id != ?', $user_id);
		}
		
		return (bool) $q->count ();
	}
}
