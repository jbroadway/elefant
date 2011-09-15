<?php

class User_OpenID extends Model {
	var $key = 'token';

	static function get_user_id ($token) {
		$u = new User_OpenID ($token);
		if (! $u->error) {
			return $u->user_id;
		}
		return false;
	}
}

?>