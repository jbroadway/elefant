<?php

$page->title = 'Page title';
$page->preview = true;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	if (! empty ($_GET['layout'])) {
		$page->layout = file_get_contents ($_GET['layout']);
	} else {
		$page->layout = '<h1>{{title}}</h1>{{ body|none }}';
	}
} else {
	$page->layout = $_POST['layout'];
}

echo '<p>This is a preview of how your layout will look.</p>';

?>