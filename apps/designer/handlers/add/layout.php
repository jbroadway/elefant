<?php

/**
 * Add layout template form.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'designer');

$f = new Form ('post', 'designer/addlayout');
$f->verify_csrf = false;
if ($f->submit ()) {
	// determine file path and make any necessary new directories
	if (strpos ($_POST['name'], '/') !== false) {
		$file_path = 'layouts/' . $_POST['name'] . '.html';
		$folder = join ('/', explode ('/', $_POST['name'], -1));
		if (! is_dir ('layouts/' . $folder)) {
			mkdir ('layouts/' . $folder, 0777, true);
		}

	// if the file is [a-z0-9_-]+ only, convert to new theme (layouts/name/name.html)
	} else {
		$file_path = 'layouts/' . $_POST['name'] . '/' . $_POST['name'] . '.html';
		if (! is_dir ('layouts/' . $_POST['name'])) {
			mkdir ('layouts/' . $_POST['name'], 0777);
		}
	}

	if (@file_put_contents ($file_path, $_POST['body'])) {
		$this->add_notification (__ ('Layout added.'));
		@chmod ($file_path, 0666);
		$this->redirect ('/designer');
	}
	$page->title = __ ('Saving Layout Failed');
	echo '<p>' . __ ('Check that your permissions are correct and try again.') . '</p>';
} else {
	$page->window_title = __ ('New Layout');
}

$o = new StdClass;
$o->body = '<!DOCTYPE html>
<html>
<head>
	<title>{{ window_title|none }}</title>
	{! admin/head !}
	{{ head|none }}
</head>
<body>
{% if title %}<h1>{{ title|none }}</h1>{% end %}

{{ body|none }}

{{ tail|none }}
</body>
</html>';

$o->failed = $f->failed;
$o = $f->merge_values ($o);
$page->add_script ('/apps/designer/css/add_layout.css');
echo $tpl->render ('designer/add/layout', $o);
