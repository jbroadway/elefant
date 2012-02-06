<?php

/**
 * Edit layout template form.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

if (! preg_match ('/^layouts\/[a-z0-9\/_-]+\.html$/i', $_GET['file'])) {
	$this->redirect ('/designer');
}

$lock = new Lock ('Designer', $_GET['file']);
if ($lock->exists ()) {
	$page->title = i18n_get ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
} else {
	$lock->add ();
}

$f = new Form ('post', 'designer/editlayout');
$f->verify_csrf = false;
if ($f->submit ()) {
	if (@file_put_contents ($_GET['file'], $_POST['body'])) {
		$this->add_notification (i18n_get ('Layout saved.'));
		@chmod ($_GET['file'], 0777);
		$lock->remove ();
		$this->redirect ('/designer');
	}
	$page->title = i18n_get ('Saving Layout Failed');
	echo '<p>' . i18n_get ('Check that your permissions are correct and try again.') . '</p>';
} else {
	$page->title = i18n_get ('Edit Layout') . ': ' . $_GET['file'];
}

$o = new StdClass;
$o->file = $_GET['file'];
$o->body = @file_get_contents ($_GET['file']);

$o->failed = $f->failed;
$o = $f->merge_values ($o);
$page->add_script ('/apps/designer/css/edit_layout.css');
echo $tpl->render ('designer/edit/layout', $o);

?>