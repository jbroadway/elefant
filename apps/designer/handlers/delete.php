<?php

/**
 * Delete handler.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'designer');

if (! preg_match ('/^(css|layouts|layouts\/[a-z0-9 _-]+|layouts\/[a-z0-9 _-]+\/[a-z0-9 _-]+)\/[a-z0-9\/ _-]+\.(css|html)$/i', $_GET['file'])) {
	$this->redirect ('/designer');
}

$lock = new Lock ('Designer', $_GET['file']);
if ($lock->exists ()) {
	$page->title = __ ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
}

if (! @unlink ($_GET['file'])) {
	$page->title = __ ('Unable to Delete File');
	echo '<p>' . __ ('Check that your permissions are correct and try again.') . '</p>';
	echo '<p><a href="/designer">' . __ ('Continue') . '</a></p>';
	return;
}

$this->add_notification (__ ('File deleted.'));
$this->redirect ('/designer');

?>