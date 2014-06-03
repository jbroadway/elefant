<?php

namespace user;

class Note extends \Model {
	public $table = '#prefix#user_notes';
	
	public $fields = array (
		'user' => array (
			'belongs_to' => 'User',
			'field_name' => 'user_id'
		)
	);
}

?>