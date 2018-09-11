<?php

/**
 * Upload handler for the file manager.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'filemanager');

$f = new Form ('post', $this);
if (! $f->verify_csrf ()) {
	$page->title = __ ('Validation Error');	
	echo '<p><a href="/filemanager">' . __ ('Back') . '</a></p>';
	return;
}

$root = getcwd () . '/' . conf('Paths','filemanager_path') . '/';

if (! FileManager::verify_folder ($_POST['path'], $root)) {
	$page->title = __ ('Invalid Path');
	echo '<p><a href="/filemanager">' . __ ('Back') . '</a></p>';
	return;
}

if (! isset ($_FILES['file'])) {
	$page->title = __ ('An Error Occurred');
	echo '<p>' . __ ('No file uploaded or file too large.') . '</p>';
	echo '<p><a href="/filemanager">' . __ ('Back') . '</a></p>';
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
		$page->title = __ ('An Error Occurred');
		echo '<p>' . __ ('Error message') . ': ' . $errors[$error] . '</p>';
		echo '<p><a href="/filemanager">' . __ ('Back') . '</a></p>';
		return;
	}
}

for ($i = 0; $i < count ($_FILES['file']['name']); $i++) {
	$_FILES['file']['name'][$i] = trim (urldecode ($_FILES['file']['name'][$i]));
	if (@file_exists ($root . $_POST['path'] . '/' . $_FILES['file']['name'][$i])) {
		$page->title = __ ('File Already Exists') . ': ' . $_FILES['file']['name'][$i];
		echo '<p>' . __ ('A file by that name already exists.') . '</p>';
		echo '<p><a href="/filemanager">' . __ ('Back') . '</a></p>';
		return;
	}
	if (strpos ($_FILES['file']['name'][$i], '..') !== false) {
		$page->title = __ ('Invalid File Name') . ': ' . $_FILES['file']['name'][$i];
		echo '<p>' . __ ('The file name contains invalid characters.') . '</p>';
		echo '<p><a href="/filemanager">' . __ ('Back') . '</a></p>';
		return;
	}
	if (preg_match ('/\.(php|phtml|pht|php3|php4|php5|phar|js|rb|py|pl|sh|bash|exe|htaccess|htpasswd)$/i', $_FILES['file']['name'][$i])) {
		$page->title = __ ('Invalid File Name') . ': ' . $_FILES['file']['name'][$i];
		echo '<p>' . __ ('Cannot upload executable files due to security.') . '</p>';
		echo '<p><a href="/filemanager">' . __ ('Back') . '</a></p>';
		return;
	}
}

$count = 0;
$errors = array ();
for ($i = 0; $i < count ($_FILES['file']['name']); $i++) {
	if (@move_uploaded_file ($_FILES['file']['tmp_name'][$i], $root . $_POST['path'] . '/' . $_FILES['file']['name'][$i])) {
		$count++;
		@chmod ($root . $_POST['path'] . '/' . $_FILES['file']['name'][$i], 0666);
		$this->hook ('filemanager/add', array (
			'file' => $_POST['path'] . '/' . $_FILES['file']['name']
		));
	} else {
		$errors[] = $_FILES['file']['name'][$i];
	}
}

if (count ($_FILES['file']) > 1) {
	if (count ($_FILES['file']['name']) === $count) {
		$this->add_notification (__ ('%d files saved.', $count));
	} else {
		$this->add_notification (__ ('%d file saved. Unable to save files: %s', $count, join (', ', $errors)));
	}
} else {
	$this->add_notification (__ ('File saved.'));
}
$this->redirect ('/filemanager?path=' . $_POST['path']);
