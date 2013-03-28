<?php

/**
 * Provides a general-purpose user notifier based on the jQuery
 * cookie and jGrowl plugins. See `apps/admin/handlers/util/notifier.php`
 * for more information.
 *
 * Usage:
 *
 *     Notifier::add_notice ('My notification.');
 */
class Notifier {
	public static $cookie_name = 'notifier_notices';
	
	/**
	 * Add a notice for the current user.
	 */
	public static function add_notice ($msg) {
		if (isset ($_COOKIE[self::$cookie_name])) {
			$msg = $_COOKIE[self::$cookie_name] . '|' . $msg;
		}
		return setcookie (self::$cookie_name, $msg, 0, '/');
	}
}

?>