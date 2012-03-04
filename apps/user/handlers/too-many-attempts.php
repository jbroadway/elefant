<?php

$page->title = i18n_get ('Too many login attempts');

printf (
	'<p>%s</p>',
	i18n_getf (
		'Your account has been locked temporarily as a security precaution. Please try again in %d minutes.',
		($appconf['User']['block_attempts_for'] / 60)
	)
);

?>