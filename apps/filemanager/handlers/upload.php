<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$root = getcwd () . '/files/';

if (! FileManager::verify_folder ($_POST['path'], $root)) {
	$page->title = 'Invalid Path';
	echo '<p><a href="/filemanager">Back</a></p>';
	return;
} elseif ($_FILES['file']['error'] > 0) {
	$page->title = 'An Error Occurred';
	echo '<p>Error message: ' . $_FILES['file']['error'] . '</p>';
	echo '<p><a href="/filemanager">Back</a></p>';
	return;
} elseif (@file_exists ($root . $_POST['path'] . '/' . $_FILES['file']['name'])) {
	$page->title = 'File Already Exists';
	echo '<p>A file by that name already exists.</p>';
	echo '<p><a href="/filemanager">Back</a></p>';
	return;
} elseif (! @move_uploaded_file ($_FILES['file']['tmp_name'], $root . $_POST['path'] . '/' . $_FILES['file']['name'])) {
	$page->title = 'An Error Occurred';
	echo '<p>Unable to save the file.</p>';
	echo '<p><a href="/filemanager">Back</a></p>';
	return;
}

@chmod ($root . $_POST['path'] . '/' . $_FILES['file']['name'], 0777);

$this->add_notification ('File saved.');
$this->redirect ('/filemanager?path=' . $_POST['path']);

?>