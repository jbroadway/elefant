<?php

/**
 * Delete handler.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

if (! preg_match ('/^(css|layouts)\/[a-z0-9_-]+\.(css|html)$/i', $_GET['file'])) {
	$this->redirect ('/designer');
}

$lock = new Lock ('Designer', $_GET['file']);
if ($lock->exists ()) {
	$page->title = i18n_get ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
}

if (! @unlink ($_GET['file'])) {
	$page->title = i18n_get ('Unable to Delete File');
	echo '<p>' . i18n_get ('Check that your permissions are correct and try again.') . '</p>';
	echo '<p><a href="/designer">' . i18n_get ('Continue') . '</a></p>';
	return;
}

$this->add_notification (i18n_get ('File deleted.'));
$this->redirect ('/designer');

?>