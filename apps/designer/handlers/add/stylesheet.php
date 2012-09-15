<?php

/**
 * Add stylesheet form.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$f = new Form ('post', 'designer/addstylesheet');
$f->verify_csrf = false;
if ($f->submit ()) {
	if (@file_put_contents ('css/' . $_POST['name'] . '.css', $_POST['body'])) {
		$this->add_notification (i18n_get ('Stylesheet added.'));
		@chmod ('layouts/' . $_POST['name'] . '.html', 0666);
		$this->redirect ('/designer');
	}
	$page->title = i18n_get ('Saving Stylesheet Failed');
	echo '<p>' . i18n_get ('Check that your permissions are correct and try again.') . '</p>';
} else {
	$page->window_title = i18n_get ('New Stylesheet');
}

$o = new StdClass;
$o->layouts = array ();
foreach (glob ('layouts/*.html') as $layout) {
	$o->layouts[] = basename ($layout, '.html');
}
foreach (glob ('layouts/*/*.html') as $layout) {
	$o->layouts[] = basename ($layout, '.html');
}

$o->failed = $f->failed;
$o = $f->merge_values ($o);
$page->add_script ('/apps/designer/css/add_stylesheet.css');
echo $tpl->render ('designer/add/stylesheet', $o);

?>