<?php

/**
 * Block delete handler.
 */

$this->require_acl ('admin', 'admin/delete', 'blocks');

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

$_POST = array_merge ($_POST, (array) $b->orig ());

if (! $b->remove ()) {
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $b->error;
	return;
}

$this->hook ('blocks/delete', $_POST);

$this->add_notification (__ ('Block deleted.'));
if (isset ($_POST['return'])) {
	$_POST['return'] = filter_var ($_POST['return'], FILTER_SANITIZE_URL);

	if (Validator::validate ($_POST['return'], 'localpath')) {
		$_POST['return'] = $_POST['return'];
	}

	$this->redirect ('/blocks/admin');
}
