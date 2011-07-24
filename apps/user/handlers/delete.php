<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$u = new User ($_GET['id']);

if (! $u->remove ()) {
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $u->error;
	return;
}

$this->hook ('user/delete', $_GET);
$this->add_notification (i18n_get ('User deleted.'));
$this->redirect ('/user/admin');

?>