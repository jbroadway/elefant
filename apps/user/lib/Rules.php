<?php

namespace user;

/**
 * Custom form rules for the user app.
 */
class Rules {
	public static function new_role ($role) {
		if (isset (\User::acl ()->rules[$role])) {
			return false;
		}
		return true;
	}
}

?>