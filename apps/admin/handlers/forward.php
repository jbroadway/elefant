<?php

/**
 * Forwards a user to the specified URL location.
 * Works as a dynamic object to be embedded
 * into the WYSIWYG editor.
 */

if (User::is_valid () && User::is ('admin')) {
	$to = isset ($data['to']) ? $data['to'] : $_GET['to'];
	printf (
		'<p>%s:</p><p><a href="%s">%s</a></p>',
		i18n_get ('This page forwards visitors to the following link'),
		$to,
		$to
	);
	return;
}

if (isset ($data['to'])) {
	$this->redirect ($data['to']);
} elseif (isset ($_GET['to'])) {
	$this->redirect ($_GET['to']);
}

?>