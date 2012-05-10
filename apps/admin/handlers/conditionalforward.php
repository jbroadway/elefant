<?php

/**
 * Forwards a user to the specified URL location.
 * Works as a dynamic object to be embedded
 * into the WYSIWYG editor.
 */

$url = isset ($data['to']) ? $data['to'] : $_GET['to'];
$user_type = isset ($data['user_type']) ? $data['user_type'] : $_GET['user_type'];

if (User::is_valid () && User::is ('admin')) {
	printf (
		'<p>%s:</p><p><a href="%s">%s</a></p>',
		i18n_getf ('This page forwards members of the %s group to the following link', $user_type),
		$url,
		$url
	);
	return;
}

$code = isset ($data['code'])
	? $data['code']
	: (isset ($_GET['code']) ? $_GET['code'] : 302);


if (User::is($user_type))
{		
	if ($code === 301) {
		$this->permenent_redirect ($url);
	}
	
		$this->redirect ($url);
}

?>
