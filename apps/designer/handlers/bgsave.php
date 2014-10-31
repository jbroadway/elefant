<?php

/**
 * Background save handler for `Save & Keep Editing` form options.
 */

$page->layout = false;
header ('Content-Type: application/json');

if (! User::require_acl ('admin') || ! User::require_acl ('designer')) {
	$res = new StdClass;
	$res->success = false;
	$res->error = 'Authorization required.';
	echo json_encode ($res);
	return;
}

$error = false;

if (! preg_match ('/^(css|layouts|layouts\/[a-z0-9 _-]+|layouts\/[a-z0-9 _-]+\/[a-z0-9 _-]+)\/[a-z0-9 _-]+\.(html|css)$/i', $_GET['file'])) {
	$res = new StdClass;
	$res->success = false;
	$res->error = 'Invalid file path';
	echo json_encode ($res);
	return;
}

if (! @file_put_contents ($_GET['file'], $_POST['body'])) {
	$error = 'Saving file failed';
} else {
	try {
		@chmod ($_GET['file'], 0666);
	} catch (Exception $e) {}
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
