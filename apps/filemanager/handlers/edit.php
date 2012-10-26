<?php

/**
 * Edits plain text files in the file manager.
 */

$this->require_admin ();

if (! isset ($_GET['file'])) {
	$this->add_notification (__ ('No file specified.'));
	$this->redirect ('/filemanager/index');
}

if (! FileManager::verify_file ($_GET['file'])) {
	$this->add_notification (__ ('Invalid file.'));
	$this->redirect ('/filemanager/index');
}

$form = new Form ('post', $this);

$form->data = array (
	'body' => file_get_contents ('files/' . $_GET['file'])
);

$page->title = __ ('Edit file') . ': ' . basename ($_GET['file']);
$page->layout = 'admin';

echo $form->handle (function ($form) {
	info ($_POST);

	$form->controller->add_notification (__ ('File saved.'));
	$form->controller->redirect ('/filemanager/index');
});

?>