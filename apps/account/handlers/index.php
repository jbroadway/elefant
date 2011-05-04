<?php

if (! User::require_login ()) {
	$page->template = 'account/login';
	return;
}

$page->template = 'account/index';

?>