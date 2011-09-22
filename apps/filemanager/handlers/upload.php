<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$root = getcwd () . '/files/';

if (! FileManager::verify_folder ($_POST['path'], $root)) {
	$page->title = i18n_get ('Invalid Path');
	echo '<p><a href="/filemanager">' . i18n_get ('Back') . '</a></p>';
	return;
} elseif ($_FILES['file']['error'] > 0) {
	$page->title = i18n_get ('An Error Occurred');
	echo '<p>' . i18n_get ('Error message') . ': ' . $_FILES['file']['error'] . '</p>';
	echo '<p><a href="/filemanager">' . i18n_get ('Back') . '</a></p>';
	return;
} elseif (@file_exists ($root . $_POST['path'] . '/' . $_FILES['file']['name'])) {
	$page->title = i18n_get ('File Already Exists');
	echo '<p>' . i18n_get ('A file by that name already exists.') . '</p>';
	echo '<p><a href="/filemanager">' . i18n_get ('Back') . '</a></p>';
	return;
} elseif (! @move_uploaded_file ($_FILES['file']['tmp_name'], $root . $_POST['path'] . '/' . $_FILES['file']['name'])) {
	$page->title = i18n_get ('An Error Occurred');
	echo '<p>' . i18n_get ('Unable to save the file.') . '</p>';
	echo '<p><a href="/filemanager">' . i18n_get ('Back') . '</a></p>';
	return;
}

@chmod ($root . $_POST['path'] . '/' . $_FILES['file']['name'], 0777);

$this->add_notification (i18n_get ('File saved.'));
$this->redirect ('/filemanager?path=' . $_POST['path']);

?>