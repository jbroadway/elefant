<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$f = new Form ('post', 'designer/addlayout');
if ($f->submit ()) {
	if (@file_put_contents ('layouts/' . $_POST['name'] . '.html', $_POST['body'])) {
		$this->add_notification (i18n_get ('Layout added.'));
		@chmod ('layouts/' . $_POST['name'] . '.html', 0777);
		$this->redirect ('/designer');
	}
	$page->title = 'Saving Layout Failed';
	echo '<p>Check that your permissions are correct and try again.</p>';
} else {
	$page->title = i18n_get ('New Layout');
}

$o = new StdClass;
$o->body = '<!DOCTYPE html>
<html>
<head>
	<title>{{ window_title }}</title>
	{! admin/head !}
	{{ head|none }}
</head>
<body>
{% if title %}<h1>{{ title }}</h1>{% end %}

{{ body|none }}

{{ tail|none }}
</body>
</html>';

$o->failed = $f->failed;
$o = $f->merge_values ($o);
echo $tpl->render ('designer/add/layout', $o);

?>