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

$root = conf('FileManager','root') .'/';
$form->root = $root;

$form->data = array (
	'body' => file_get_contents ($root . $_GET['file'])
);

$page->title = __ ('Edit file') . ': ' . basename ($_GET['file']);
$page->layout = 'admin';

echo $form->handle (function ($form) {
	if (! file_put_contents ($form->root . $_GET['file'], $_POST['body'])) {
		$form->controller->add_notification (__ ('Unable to write to the file. Please check your folder permissions and try again.'));
		return false;
	}

	$form->controller->add_notification (__ ('File saved.'));
	$form->controller->redirect ('/filemanager/index');
});

?>