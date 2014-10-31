<?php

namespace user;

use User;
use DB;
use I18n;

class Note extends \Model {
	public $table = '#prefix#user_notes';
	
	public $fields = array (
		'user' => array (
			'belongs_to' => 'User',
			'field_name' => 'user_id'
		)
	);
	
	/**
	 * Get all notes for the specified user.
	 */
	public static function for_user ($id) {
		$o = new Note;
		$u = new User;
		$res = DB::fetch (
			'select n.*, u.name as made_by_name
			 from ' . $o->table . ' n, ' . $u->table . ' u
			 where n.user_id = ?
			 and n.made_by = u.id
			 order by n.ts desc',
			$id
		);
		foreach ($res as $k => $v) {
			$res[$k]->date = I18n::short_date_time ($v->ts);
		}
		return $res;
	}
}
