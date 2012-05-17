<?php

/**
 * Upload handler for the file manager.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$root = getcwd () . '/files/';

if (! FileManager::verify_folder ($_POST['path'], $root)) {
	$page->title = i18n_get ('Invalid Path');
	echo '<p><a href="/filemanager">' . i18n_get ('Back') . '</a></p>';
	return;
}

foreach ($_FILES['file']['error'] as $error) {
	if ($error > 0) {
		$page->title = i18n_get ('An Error Occurred');
		echo '<p>' . i18n_get ('Error message') . ': ' . $error . '</p>';
		echo '<p><a href="/filemanager">' . i18n_get ('Back') . '</a></p>';
		return;
	}
}

for ($i = 0; $i < count ($_FILES['file']['name']); $i++) {
	if (@file_exists ($root . $_POST['path'] . '/' . $_FILES['file']['name'][$i])) {
		$page->title = i18n_get ('File Already Exists') . ': ' . $_FILES['file']['name'][$i];
		echo '<p>' . i18n_get ('A file by that name already exists.') . '</p>';
		echo '<p><a href="/filemanager">' . i18n_get ('Back') . '</a></p>';
		return;
	}
}

$count = 0;
$errors = array ();
for ($i = 0; $i < count ($_FILES['file']['name']); $i++) {
	if (@move_uploaded_file ($_FILES['file']['tmp_name'][$i], $root . $_POST['path'] . '/' . $_FILES['file']['name'][$i])) {
		$count++;
		@chmod ($root . $_POST['path'] . '/' . $_FILES['file']['name'][$i], 0777);
		$this->hook ('filemanager/add', array (
			'file' => $_POST['path'] . '/' . $_FILES['file']['name']
		));
	} else {
		$errors[] = $_FILES['file']['name'][$i];
	}
}

if (count ($_FILES['file']) > 1) {
	if (count ($_FILES['file']['name']) === $count) {
		$this->add_notification (i18n_getf ('%d files saved.', $count));
	} else {
		$this->add_notification (i18n_getf ('%d file saved. Unable to save files: %s', $count, join (', ', $errors)));
	}
} else {
	$this->add_notification (i18n_get ('File saved.'));
}
$this->redirect ('/filemanager?path=' . $_POST['path']);

?>