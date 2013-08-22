<?php

/**
 * Add stylesheet form.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'designer');

$f = new Form ('post', 'designer/addstylesheet');
$f->verify_csrf = false;
if ($f->submit ()) {
	if (@file_put_contents ('css/' . $_POST['name'] . '.css', $_POST['body'])) {
		$this->add_notification (__ ('Stylesheet added.'));
		@chmod ('css/' . $_POST['name'] . '.css', 0666);
		$this->redirect ('/designer');
	}
	$page->title = __ ('Saving Stylesheet Failed');
	echo '<p>' . __ ('Check that your permissions are correct and try again.') . '</p>';
} else {
	$page->window_title = __ ('New Stylesheet');
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