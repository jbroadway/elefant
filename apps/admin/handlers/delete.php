<?php

/**
 * Deletes a web page.
 */

$this->require_acl ('admin', 'admin/delete');

$page->layout = 'admin';

if (! isset ($_POST['page'])) {
	$this->redirect ('/');
}

$lock = new Lock ('Webpage', $_POST['page']);
if ($lock->exists ()) {
	$page->title = __ ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
}

$wp = new Webpage ($_POST['page']);

if (! $wp->remove ()) {
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $wp->error;
	return;
}

$cache->delete ('_admin_page_' . $_POST['page']);
$this->add_notification (__ ('Page deleted.'));
$this->hook ('admin/delete', $_POST);
$this->redirect ( isset ($_POST['admin']) ? '/admin/pages' : '/' );

?>