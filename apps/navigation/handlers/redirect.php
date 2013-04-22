<?php

/**
 * Redirect users to the language homepage.
 */

$url = Link::href ($i18n->language);

if (User::require_admin ()) {
	printf (
		'<p>%s:</p><p><a href="%s">%s</a></p>',
		__ ('This page forwards visitors to the following link'),
		$url,
		$url
	);
	return;
}

$this->permanent_redirect ($url);

?>