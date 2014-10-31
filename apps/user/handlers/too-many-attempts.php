<?php

$page->title = __ ('Too many login attempts');

printf (
	'<p>%s</p>',
	__ (
		'Your account has been locked temporarily as a security precaution. Please try again in %d minutes.',
		($appconf['User']['block_attempts_for'] / 60)
	)
);
