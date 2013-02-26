<?php

/**
 * Forwards a user to the specified URL location
 * if they match a specific access level.
 * Works as a dynamic object to be embedded
 * into the WYSIWYG editor.
 */

$url = isset ($data['to']) ? $data['to'] : $_GET['to'];
$user_type = isset ($data['user_type']) ? $data['user_type'] : $_GET['user_type'];

if (User::is_valid () && User::is ('admin')) {
	printf (
		'<p>%s:</p><p><a href="%s">%s</a></p>',
		__ ('This page forwards members of the %s group to the following link', $user_type),
		$url,
		$url
	);
	return;
}

if (User::is ($user_type)) {
	$this->redirect ($url);
}

?>