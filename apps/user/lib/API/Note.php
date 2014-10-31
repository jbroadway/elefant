<?php

namespace user\API;

use User;
use Restful;

class Note extends Restful {
	public function get__default ($id) {
		return \user\Note::for_user ($id);
	}

	public function post_add () {
		if (! isset ($_POST['user'])) return $this->error ('Missing parameter: user');
		if (! isset ($_POST['note'])) return $this->error ('Missing parameter: note');
		
		$note = new \user\Note (array (
			'user_id' => $_POST['user'],
			'ts' => gmdate ('Y-m-d H:i:s'),
			'made_by' => User::val ('id'),
			'note' => $_POST['note']
		));
		if (! $note->put ()) {
			error_log ($note->error);
			return $this->error ('An unexpected error occurred.');
		}
		return $this->get__default ($_POST['user']);
		//return $note->orig ();
	}
	
	public function post_delete () {
		if (! isset ($_POST['id'])) return $this->error ('Missing parameter: id');
		if (! isset ($_POST['user'])) return $this->error ('Missing parameter: user');
		
		$note = new \user\Note ($_POST['id']);
		if ($note->error) {
			error_log ($note->error);
			return $this->error ('An unexpected error occurred.');
		}
		
		if (! $note->remove ()) {
			error_log ($note->error);
			return $this->error ('An unexpected error occurred.');
		}
		return $this->get__default ($_POST['user']);
		//return true;
	}
}
