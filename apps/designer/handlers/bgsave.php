<?php

/**
 * Background save handler for `Save & Keep Editing` form options.
 */

$page->layout = false;
header ('Content-Type: application/json');

if (! User::require_admin ()) {
	$res = new StdClass;
	$res->success = false;
	$res->error = 'Authorization required.';
	echo json_encode ($res);
	return;
}

$error = false;

if (! preg_match ('/^(css|layouts|layouts\/[a-z0-9_-]+)\/[a-z0-9_-]+\.(html|css)$/i', $_GET['file'])) {
	$this->redirect ('/designer');
}

if (! @file_put_contents ($_GET['file'], $_POST['body'])) {
	$error = 'Saving file failed';
} else {
	@chmod ($_GET['file'], 0777);
}

$res = new StdClass;
if ($error) {
	$res->success = false;
	$res->error = $error;
} else {
	$res->success = true;
	$res->data = $_GET['id'];
}

echo json_encode ($res);

?>