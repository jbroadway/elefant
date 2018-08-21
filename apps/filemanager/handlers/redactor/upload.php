<?php

/**
 * Upload handler for the wysiwyg editor.
 */

$page->layout = false;

if (! User::require_acl ('admin') || ! User::require_acl ('filemanager')) {
	echo json_encode (array ('error' => __ ('Must be logged in to upload')));
	return;
}

$root = getcwd () . '/' . conf('Paths','filemanager_path') . '/';

if (! isset ($_FILES['file'])) {
	echo json_encode (array ('error' => __ ('File upload field not set.')));
	return;
}

foreach ($_FILES['file']['error'] as $error) {
	if ($error > 0) {
		$errors = array (
			1 => __ ('File size is too large.'),
			2 => __ ('File size is too large.'),
			3 => __ ('The file was only partially uploaded.'),
			4 => __ ('No file was uploaded.'),
			6 => __ ('Missing a temporary folder, check your PHP setup.'),
			7 => __ ('Failed to write the file to disk.'),
			8 => __ ('A PHP extension stopped the file upload.')
		);
		echo json_encode (array ('error' => $errors[$error]));
		return;
	}
}

// some browsers may urlencode the file name
$_FILES['file']['name'] = urldecode ($_FILES['file']['name']);

if (preg_match ('/\.(php|phtml|pht|php3|php4|php5|phar|js|rb|py|pl|sh|bash|exe)$/i', $_FILES['file']['name'])) {
	echo json_encode (array ('error' => __ ('Cannot upload executable files due to security.')));
	return;
}

if (file_exists ($root . $_FILES['file']['name'])) {
	echo json_encode (array ('error' => __ ('File already exists') . ': ' . $_FILES['file']['name']));
	return;
}

if (strpos ($_FILES['file']['name'], '..') !== false) {
	echo json_encode (array ('error' => __ ('Invalid file name') . ': ' . $_FILES['file']['name']));
	return;
}

if (@move_uploaded_file ($_FILES['file']['tmp_name'], $root . $_FILES['file']['name'])) {
	@chmod ($root . $_FILES['file']['name'], 0666);
	$this->hook ('filemanager/add', array (
		'file' => '/' . $_FILES['file']['name']
	));
	echo stripslashes (
		json_encode (
			array (
				'filelink' => '/' . conf('Paths','filemanager_path') . '/' . $_FILES['file']['name'],
				'filename' => $_FILES['file']['name']
			)
		)
	);
} else {
	echo json_encode (array ('error' => __ ('Failed to save file') . ': ' . $_FILES['file']['name']));
}
