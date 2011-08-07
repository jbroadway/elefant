<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$f = new Form ('post', 'designer/addstylesheet');
if ($f->submit ()) {
	if (@file_put_contents ('css/' . $_POST['name'] . '.css', $_POST['body'])) {
		$this->add_notification (i18n_get ('Stylesheet added.'));
		@chmod ('layouts/' . $_POST['name'] . '.html', 0777);
		$this->redirect ('/designer');
	}
	$page->title = 'Saving Stylesheet Failed';
	echo '<p>Check that your permissions are correct and try again.</p>';
} else {
	$page->title = i18n_get ('New Stylesheet');
}

$o = new StdClass;
$o->layouts = array ();
foreach (glob ('layouts/*.html') as $layout) {
	$o->layouts[] = basename ($layout, '.html');
}

$o->failed = $f->failed;
$o = $f->merge_values ($o);
echo $tpl->render ('designer/add/stylesheet', $o);

?>