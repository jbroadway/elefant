<?php

namespace user\API;

use Restful;

class Link extends Restful {
	public function get__default ($id) {
		return \user\Link::for_user ($id);
	}

	public function post_add () {
		if (! isset ($_POST['user'])) return $this->error ('Missing parameter: user');
		if (! isset ($_POST['service'])) return $this->error ('Missing parameter: service');
		if (! isset ($_POST['handle'])) return $this->error ('Missing parameter: handle');

		$link = new \user\Link (array (
			'user_id' => $_POST['user'],
			'service' => $_POST['service'],
			'handle' => $_POST['handle']
		));
		if (! $link->put ()) {
			error_log ($link->error);
			return $this->error ('An unexpected error occurred.');
		}
		return $this->get__default ($_POST['user']);
		//return $link->orig ();
	}
	
	public function post_delete () {
		if (! isset ($_POST['id'])) return $this->error ('Missing parameter: id');
		if (! isset ($_POST['user'])) return $this->error ('Missing parameter: user');

		$link = new \user\Link ($_POST['id']);
		if ($link->error) {
			error_log ($link->error);
			return $this->error ('An unexpected error occurred.');
		}

		if (! $link->remove ()) {
			error_log ($link->error);
			return $this->error ('An unexpected error occurred.');
		}
		return $this->get__default ($_POST['user']);
		//return true;
	}
}
