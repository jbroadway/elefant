<?php

$page->layout = 'admin';

if (! User::require_login ()) {
	$page->title = 'Elefant Admin';
	echo '<p>Please log in to continue.</p>';
	echo $this->run ('user/login');
	return;
}

header ('Location: /');
exit;

?>