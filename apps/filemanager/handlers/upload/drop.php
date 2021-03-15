<?php

/**
 * Upload handler for the filemanager/util/browser dialog.
 */

$page->layout = false;

if (! User::require_acl ('admin') || ! User::require_acl ('filemanager')) {
	echo json_encode (array ('success' => false, 'error' => __ ('Must be logged in to upload')));
	return;
}

$f = new Form ('post', $this);
$_POST['_token_'] = $_POST['token'];
if (! $f->verify_csrf ('/filemanager')) {
	echo json_encode (array ('success' => false, 'error' => __ ('Validation error')));
	return;
}

$root = getcwd () . '/' . conf('Paths','filemanager_path') . '/';

if (! FileManager::verify_folder ($_POST['path'], $root)) {
	echo json_encode (array ('success' => false, 'error' => __ ('Invalid path')));
	return;
}

if (! isset ($_FILES['file'])) {
	echo json_encode (array ('success' => false, 'error' => __ ('No file uploaded or file too large.')));
	return;
}

if (isset ($_FILES['file']['error']) && $_FILES['file']['error'] > 0) {
	$errors = array (
		1 => __ ('File size is too large.'),
		2 => __ ('File size is too large.'),
		3 => __ ('The file was only partially uploaded.'),
		4 => __ ('No file was uploaded.'),
		6 => __ ('Missing a temporary folder, check your PHP setup.'),
		7 => __ ('Failed to write the file to disk.'),
		8 => __ ('A PHP extension stopped the file upload.')
	);
	echo json_encode (array ('success' => false, 'error' => $errors[$_FILES['file']['error']]));
	return;
}

// some browsers may urlencode the file name
$_FILES['file']['name'] = trim (urldecode ($_FILES['file']['name']));

if (preg_match ('/\.(php|phtml|pht|php3|php4|php5|phar|js|rb|py|pl|sh|bash|exe|htaccess|htpasswd)$/i', $_FILES['file']['name'])) {
	echo json_encode (array ('success' => false, 'error' => __ ('Cannot upload executable files due to security.')));
	return;
}

if (strpos ($_FILES['file']['name'], '..') !== false) {
	echo json_encode (array ('success' => false, 'error' => __ ('The file name contains invalid characters.')));
	return;
}

if (@move_uploaded_file ($_FILES['file']['tmp_name'], $root . $_POST['path'] . '/' . $_FILES['file']['name'])) {
	@chmod ($root . $_POST['path'] . '/' . $_FILES['file']['name'], 0666);
	$this->hook ('filemanager/add', array (
		'file' => $_POST['path'] . '/' . $_FILES['file']['name']
	));
} else {
	echo json_encode (array ('success' => false, 'error' => __ ('Unable to save the file') . ': ' . $_FILES['file']['name']));
	return;
}

echo json_encode (array ('success' => true, 'data' => __ ('File saved.')));
