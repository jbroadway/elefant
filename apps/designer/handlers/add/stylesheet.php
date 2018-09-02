<?php

/**
 * Add stylesheet form.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'designer');

$f = new Form ('post', 'designer/addstylesheet');

if ($f->submit ()) {
	// determine file path and make any necessary new directories
	if (strpos ($_POST['name'], '/') !== false) {
		$file_path = 'layouts/' . $_POST['name'] . '.css';
		$folder = join ('/', explode ('/', $_POST['name'], -1));
		if (! is_dir ('layouts/' . $folder)) {
			mkdir ('layouts/' . $folder, 0777, true);
		}

	// if the file is [a-z0-9_-]+ only, convert to new theme (layouts/name/style.css)
	} else {
		$file_path = 'layouts/' . $_POST['name'] . '/style.css';
		if (! is_dir ('layouts/' . $_POST['name'])) {
			mkdir ('layouts/' . $_POST['name'], 0777);
		}
	}

	if (@file_put_contents ($file_path, $_POST['body'])) {
		$this->add_notification (__ ('Stylesheet added.'));
		@chmod ($file_path, 0666);
		$this->redirect ('/designer');
	}
	$page->title = __ ('Saving Stylesheet Failed');
	echo '<p>' . __ ('Check that your permissions are correct and try again.') . '</p>';
} else {
	$page->window_title = __ ('New Stylesheet');
}

$o = new StdClass;
$o->layouts = array ();

$files = glob ('layouts/*.html');
if (is_array ($files)) {
	foreach ($files as $layout) {
		$o->layouts[] = basename ($layout, '.html');
	}
}

$files = glob ('layouts/*/*.html');
if (is_array ($files)) {
	foreach ($files as $layout) {
		$o->layouts[] = basename ($layout, '.html');
	}
}

$o->failed = $f->failed;
$o = $f->merge_values ($o);
$this->run ('admin/util/i18n');
$page->add_script ('/apps/designer/css/stylesheet.css?v=2');
echo $tpl->render ('designer/add/stylesheet', $o);
