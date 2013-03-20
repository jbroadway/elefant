<?php

/**
 * Block delete handler.
 */

$this->require_admin ();

$page->layout = 'admin';

if (! isset ($_POST['id'])) {
	$this->redirect ('/blocks/admin');
}

$lock = new Lock ('Block', $_POST['id']);
if ($lock->exists ()) {
	$page->title = __ ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
}

$b = new Block ($_POST['id']);

if (! $b->remove ()) {
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $b->error;
	return;
}

$this->hook ('blocks/delete', $_POST);

$this->add_notification (__ ('Block deleted.'));
if (! isset ($_POST['return'])) {
	$this->redirect ('/blocks/admin');
} else {
	$this->redirect ($_POST['return']);
}

?>