<?php

/**
 * Restores a previous version of a Model object, replacing the
 * current version, and adding a new version to the history
 * as well.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$ver = new Versions ($_GET['id']);

$lock = new Lock ($ver->class, $ver->pkey);
if ($lock->exists ()) {
	$page->title = __ ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
} else {
	$lock->add ();
}

$obj = $ver->restore ();
$obj->put ();
if ($obj->error) {
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $obj->error;
	return;
}
Versions::add ($obj);

$this->add_notification (__ ('Item restored.'));
if ($ver->class == 'Webpage') {
	$cache->delete ('_admin_page_' . $obj->id);
	$this->redirect ('/' . $obj->id);
}
$this->redirect ('/');

?>
