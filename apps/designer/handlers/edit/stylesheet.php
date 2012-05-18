<?php

/**
 * Edit stylesheet form.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

if (! preg_match ('/^(css|layouts\/[a-z0-9_-]+)\/[a-z0-9\._-]+\.css$/i', $_GET['file'])) {
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

$f = new Form ('post', 'designer/editstylesheet');
$f->verify_csrf = false;
if ($f->submit ()) {
	if (@file_put_contents ($_GET['file'], $_POST['body'])) {
		$this->add_notification (i18n_get ('Stylesheet saved.'));
		@chmod ($_GET['file'], 0777);
		$lock->remove ();
		$this->redirect ('/designer');
	}
	$page->title = i18n_get ('Saving Stylesheet Failed');
	echo '<p>' . i18n_get ('Check that your permissions are correct and try again.') . '</p>';
} else {
	$page->title = i18n_get ('Edit Stylesheet') . ': ' . $_GET['file'];
}

$o = new StdClass;
$o->file = $_GET['file'];
$o->body = @file_get_contents ($_GET['file']);
$o->layouts = array ();

$files = glob ('layouts/*.html');
if ($files) {
	foreach ($files as $layout) {
		$o->layouts[] = basename ($layout, '.html');
	}
}

$files = glob ('layouts/*/*.html');
if ($files) {
	foreach ($files as $layout) {
		$o->layouts[] = basename ($layout, '.html');
	}
}

$o->failed = $f->failed;
$o = $f->merge_values ($o);
$page->add_script ('/apps/designer/css/edit_stylesheet.css');
echo $tpl->render ('designer/edit/stylesheet', $o);

?>