<?php

/**
 * User delete handler.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$u = new User ($_GET['id']);

if (! $u->remove ()) {
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $u->error;
	return;
}

$this->hook ('user/delete', $_GET);
$this->add_notification (__ ('User deleted.'));
$this->redirect ('/user/admin');

?>