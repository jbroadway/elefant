<?php

namespace user;

class Link extends \Model {
	public $table = '#prefix#user_links';
	
	public $fields = array (
		'user' => array (
			'belongs_to' => 'User',
			'field_name' => 'user_id'
		)
	);
}

?>