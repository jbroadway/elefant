<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$lock = new Lock ('Block', $_GET['id']);
if ($lock->exists ()) {
	$page->title = i18n_get ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
}

$b = new Block ($_GET['id']);

if (! $b->remove ()) {
	$page->title = i18n_get ('An Error Occurred');
	echo i18n_get ('Error Message') . ': ' . $b->error;
	return;
}

$this->hook ('blocks/delete', $_GET);

$this->add_notification ('Block deleted.');
if (! isset ($_GET['return'])) {
	$this->redirect ('/blocks/admin');
} else {
	$this->redirect ($_GET['return']);
}

?>