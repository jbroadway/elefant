<?php

/**
 * Forwards a user to the specified URL location.
 * Works as a dynamic object to be embedded
 * into the WYSIWYG editor.
 */

$url = isset ($data['to']) ? $data['to'] : $_GET['to'];

if (User::require_admin ()) {
	printf (
		'<p>%s:</p><p><a href="%s">%s</a></p>',
		__ ('This page forwards visitors to the following link'),
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
$this->redirect ($url);
