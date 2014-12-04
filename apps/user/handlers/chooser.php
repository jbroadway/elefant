<?php

/**
 * Provides a JSON list of users to the user/util/userchooser dialog.
 */

$this->require_acl ('admin', 'user');

$page->layout = false;

$users = User::query ('id, name, email')
	->order ('name asc')
	->fetch_orig ();

header ('Content-Type: application/json; charset=utf8');

$out = json_encode ($users);
$error = null;

switch (json_last_error ()) {
	case JSON_ERROR_NONE:
		echo $out;
		break;
	default:
		echo json_encode (array (
			'success' => false,
			'error' => json_last_error_msg ()
		));
}
