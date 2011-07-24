<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$ver = new Versions ($_GET['id']);

$lock = new Lock ($ver->class, $ver->pkey);
if ($lock->exists ()) {
	$page->title = i18n_get ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
} else {
	$lock->add ();
}

$obj = $ver->restore ();
$obj->put ();
if ($obj->error) {
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $obj->error;
	return;
}
Versions::add ($obj);

$this->add_notification ('Item restored.');
if ($ver->class == 'Webpage') {
	$this->redirect ('/' . $obj->id);
}
$this->redirect ('/');

?>