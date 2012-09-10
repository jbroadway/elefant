<?php

/**
 * Add layout template form.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$f = new Form ('post', 'designer/addlayout');
$f->verify_csrf = false;
if ($f->submit ()) {
	if (@file_put_contents ('layouts/' . $_POST['name'] . '.html', $_POST['body'])) {
		$this->add_notification (i18n_get ('Layout added.'));
		@chmod ('layouts/' . $_POST['name'] . '.html', 0666);
		$this->redirect ('/designer');
	}
	$page->title = i18n_get ('Saving Layout Failed');
	echo '<p>' . i18n_get ('Check that your permissions are correct and try again.') . '</p>';
} else {
	$page->window_title = i18n_get ('New Layout');
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

?>