<?php

/**
 * Live preview handler for layout template forms.
 */

$this->require_admin ();

$page->title = __ ('Page title');
$page->preview = true;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$page->layout = '<h1>{{title}}</h1>{{ body|none }}';
	if (! empty ($_GET['layout'])) {
		if (preg_match('/^layouts\/[a-z0-9\/ _-]+\.html$/i', $_GET['layout']) &&
			file_exists($_GET['layout']) ) {
			$page->layout = file_get_contents ($_GET['layout']);
		}
	}
} else {
	$page->layout = $_POST['layout'];
}

echo '<p>' . __ ('This is a preview of how your layout will look.') . '</p>';
