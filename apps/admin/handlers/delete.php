<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$lock = new Lock ('Webpage', $_GET['page']);
if ($lock->exists ()) {
	$page->title = i18n_get ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
}

$wp = new Webpage ($_GET['page']);

if (! $wp->remove ()) {
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $wp->error;
	return;
}

$this->add_notification (i18n_get ('Page deleted.'));
$this->hook ('admin/delete', $_GET);
$this->redirect ('/');

?>