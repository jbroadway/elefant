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
		i18n_get ('This page forwards members of the '.$user_type.' group the following link'),
		$url,
		$url
	);
	return;
}

$code = isset ($data['code'])
	? $data['code']
	: (isset ($_GET['code']) ? $_GET['code'] : 302);

if ($code === 301) {
	$this->permenent_redirect ($url);
}

if (User::is($user_type))
{
	$this->redirect ($url);
}

?>