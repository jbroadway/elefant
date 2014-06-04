<?php

namespace user;

use DB;

class Link extends \Model {
	public $table = '#prefix#user_links';
	
	public $fields = array (
		'user' => array (
			'belongs_to' => 'User',
			'field_name' => 'user_id'
		)
	);
	
	/**
	 * Get all links for the specified user.
	 */
	public static function for_user ($id) {
		$o = new Link;
		$res = DB::fetch (
			'select * from ' . $o->table . '
			 where user_id = ?
			 order by service asc',
			$id
		);
		foreach ($res as $k => $v) {
			$res[$k]->link = self::get_link ($v->service, $v->handle);
		}
		return $res;
	}
	
	/**
	 * Filter a handle by service to return a link.
	 */
	public static function get_link ($service, $handle) {
		if (preg_match ('/^https?\:\/\//', $handle)) {
			return $handle;
		}

		switch ($service) {
			case 'Twitter':
				return 'https://twitter.com/' . ltrim ($handle, '@');

			case 'Instagram':
				return 'http://instagram.com/' . $handle;

			case 'Tumblr':
				return 'http://' . $handle . '.tumblr.com/';

			case 'Facebook':
			case 'Google+':
			case 'Website':
				return 'http://' . $handle;
		}
	}
}

?>