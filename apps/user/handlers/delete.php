<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$u = new User ($_GET['id']);

if (! $u->remove ()) {
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $u->error;
	return;
}

$this->hook ('user/delete', $_GET);
$page->title = 'User Deleted';
echo '<p><a href="/user/admin">Continue</a></p>';

?>