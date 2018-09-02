<?php

/**
 * Edit layout template form.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'designer');

require_once ('apps/designer/lib/Functions.php');

if (! preg_match ('/^(layouts|layouts\/[a-z0-9 _-]+|layouts\/[a-z0-9 _-]+\/[a-z0-9 _-]+)\/[a-z0-9 _-]+\.html$/i', $_GET['file'])) {
	$this->redirect ('/designer');
}

$lock = new Lock ('Designer', $_GET['file']);
if ($lock->exists ()) {
	$page->title = __ ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
} else {
	$lock->add ();
}

$f = new Form ('post', 'designer/editlayout');

if ($f->submit ()) {
	if (@file_put_contents ($_GET['file'], $_POST['body'])) {
		$this->add_notification (__ ('Layout saved.'));
		try {
			@chmod ($_GET['file'], 0666);
		} catch (Exception $e) {}
		$lock->remove ();
		$this->redirect ('/designer');
	}
	$page->title = __ ('Saving Layout Failed');
	echo '<p>' . __ ('Check that your permissions are correct and try again.') . '</p>';
} else {
	$page->window_title = __ ('Edit Layout') . ': ' . Template::sanitize ($_GET['file']);
}

$o = new StdClass;
$o->file = $_GET['file'];
$o->body = @file_get_contents ($_GET['file']);

$o->failed = $f->failed;
$o = $f->merge_values ($o);
$this->run ('admin/util/i18n');
$page->add_script ('/apps/designer/css/layout.css?v=2');
$page->add_script ('/apps/designer/js/jquery.bindWithDelay.js');
echo $tpl->render ('designer/edit/layout', $o);
