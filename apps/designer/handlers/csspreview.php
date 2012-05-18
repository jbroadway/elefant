<?php

/**
 * Live preview handler for stylesheet forms.
 */

$this->require_admin ();

$page->title = i18n_get ('Page title');
$page->preview = true;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	if (! empty ($_GET['layout'])) {
		if (file_exists ('layouts/' . $_GET['layout'] . '.html')) {
			$page->layout = file_get_contents ('layouts/' . $_GET['layout'] . '.html');
		} elseif (file_exists ('layouts/' . $_GET['layout'] . '/' . $_GET['layout'] . '.html')) {
			$page->layout = file_get_contents ('layouts/' . $_GET['layout'] . '/' . $_GET['layout'] . '.html');
		} else {
			$page->layout = file_get_contents ('layouts/default.html');
		}
	} else {
		$page->layout = file_get_contents ('layouts/default.html');
	}
	if ($_GET['css']) {
		$page->layout = str_replace ('</head>', '<style>' . file_get_contents ($_GET['css']) . '</style></head>', $page->layout);
	}
} else {
	if (! empty ($_POST['layout'])) {
		if (file_exists ('layouts/' . $_POST['layout'] . '.html')) {
			$page->layout = file_get_contents ('layouts/' . $_POST['layout'] . '.html');
		} elseif (file_exists ('layouts/' . $_POST['layout'] . '/' . $_POST['layout'] . '.html')) {
			$page->layout = file_get_contents ('layouts/' . $_POST['layout'] . '/' . $_POST['layout'] . '.html');
		} else {
			$page->layout = file_get_contents ('layouts/default.html');
		}
	} else {
		$page->layout = file_get_contents ('layouts/default.html');
	}
	if ($_POST['css']) {
		$page->layout = str_replace ('</head>', '<style>' . $_POST['css'] . '</style></head>', $page->layout);
	}	
}

echo '<p>' . i18n_get ('This is a preview of how your layout will look.') . '</p>';

?>