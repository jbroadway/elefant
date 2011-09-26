<?php

$page->title = i18n_get ('Page title');
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

echo '<p>' . i18n_get ('This is a preview of how your layout will look.') . '</p>';

?>