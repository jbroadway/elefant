<?php

/**
 * Provides a JSON list of users to the user/util/userchooser dialog.
 */

$this->require_acl ('admin', 'user');

$page->layout = false;

$users = User::query ('id, name, email')
	->order ('name asc')
	->fetch_orig ();

header ('Content-Type: application/json');

echo json_encode ($users);

?>