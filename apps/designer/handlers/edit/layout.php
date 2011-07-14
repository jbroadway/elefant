<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

if (! preg_match ('/^layouts\/[a-z0-9_-]+\.html$/i', $_GET['file'])) {
	header ('Location: /designer');
	exit;
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
if ($f->submit ()) {
	if (@file_put_contents ($_GET['file'], $_POST['body'])) {
		$page->title = i18n_get ('Layout Saved');
		echo '<p><a href="/designer">' . i18n_get ('Continue') . '</a></p>';
		@chmod ($_GET['file'], 0777);
		$lock->remove ();
		return;
	}
	$page->title = 'Saving Layout Failed';
	echo '<p>Check that your permissions are correct and try again.</p>';
} else {
	$page->title = i18n_get ('Edit Layout') . ': ' . $_GET['file'];
}

$o = new StdClass;
$o->body = @file_get_contents ($_GET['file']);

$o->failed = $f->failed;
$o = $f->merge_values ($o);
echo $tpl->render ('designer/edit/layout', $o);

?>