<?php

/**
 * Live preview handler for layout template forms.
 */

$this->require_admin ();

$this->run ('admin/util/i18n');

$page->title = __ ('Page title');
$page->preview = true;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$page->layout = '<h1>{{title}}</h1>{{ body|none }}';
	if (! empty ($_GET['layout'])) {
		if (preg_match ('/^layouts\/[a-z0-9\/ _-]+\.html$/i', $_GET['layout']) &&
			file_exists ($_GET['layout']) ) {
			$page->layout = file_get_contents ($_GET['layout']);
		}
	}
} else {
	require_once ('apps/designer/lib/Functions.php');
	
	if (invalid_php_functions ($_POST['layout'])) {
		$page->layout = false;
		echo '<p>' . __ ('Invalid PHP functions detected in your layout template. Please remove to re-enable preview.') . '</p>';
		return;
	}

	$page->layout = $_POST['layout'];
}

echo '<p>' . __ ('This is a preview of how your layout will look.') . '</p>';
