<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

if (! preg_match ('/^css\/[a-z0-9_-]+\.css$/i', $_GET['file'])) {
	header ('Location: /designer');
	exit;
}

$f = new Form ('post', 'designer/editstylesheet');
if ($f->submit ()) {
	if (@file_put_contents ($_GET['file'], $_POST['body'])) {
		$page->title = i18n_get ('Stylesheet Saved');
		echo '<p><a href="/designer">' . i18n_get ('Continue') . '</a></p>';
		@chmod ($_GET['file'], 0777);
		return;
	}
	$page->title = 'Saving Stylesheet Failed';
	echo '<p>Check that your permissions are correct and try again.</p>';
} else {
	$page->title = i18n_get ('Edit Stylesheet') . ': ' . $_GET['file'];
}

$o = new StdClass;
$o->body = @file_get_contents ($_GET['file']);

$o->failed = $f->failed;
$o = $f->merge_values ($o);
echo $tpl->render ('designer/edit/stylesheet', $o);

?>