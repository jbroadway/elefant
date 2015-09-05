<?php

/**
 * Redirect users to the current language homepage link.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('navigation/redirect');
 *
 * In a template, call it like this:
 *
 *     {! navigation/redirect !}
 *
 * Also available in the dynamic objects menu as "Multilingual Homepage Redirect".
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
