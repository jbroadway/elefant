<?php

namespace user;

/**
 * Stores session IDs for user logins.
 *
 * Fields:
 *
 * - session_id
 * - expires
 * - user_id
 */
class Session extends \Model {
	public $table = '#prefix#user_session';
	
	public $key = 'session_id';
	
	public static $expires = 2592000; // 1 month
	
	/**
	 * Generate a unique ID for a new session.
	 */
	public static function generate_id () {
		return md5 (uniqid (mt_rand (), 1));
	}
	
	/**
	 * Create a new session for a user. Returns the new Session
	 * object.
	 */
	public static function create ($user) {
		$s = new Session (array (
			'session_id' => self::generate_id (),
			'expires' => gmdate ('Y-m-d H:i:s', time () + self::$expires),
			'user_id' => $user
		));
		$try = 0;
		while (! $s->put ()) {
			$s->session_id = self::generate_id ();
			$try++;
			if ($try === 10) {
				// give up after 10 tries
				return false;
			}
		}
		return $s;
	}
	
	/**
	 * Fetch user for a given session ID.
	 */
	public static function fetch_user ($id) {
		return \DB::single (
			'select u.* from #prefix#user_session s left join #prefix#user u on s.user_id = u.id
			 where s.session_id = ? and s.expires > ?',
			 $id,
			 gmdate ('Y-m-d H:i:s')
		);
	}
	
	/**
	 * Clear a single session ID.
	 */
	public static function clear ($id) {
		return \DB::execute (
			'delete from #prefix#user_session where session_id = ?',
			$id
		);
	}
	
	/**
	 * Clear all session IDs for a given user.
	 */
	public static function clear_all ($user) {
		return \DB::execute (
			'delete from #prefix#user_session where user_id = ?',
			$user
		);
	}
	
	/**
	 * Clear all expired sessions.
	 */
	public static function clear_expired () {
		return \DB::execute (
			'delete from #prefix#user_session where expires <= ?',
			gmdate ('Y-m-d H:i:s')
		);
	}
}
