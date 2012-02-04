<?php

/**
 * Install an app or theme from a zip or Github repo.
 */

$page->layout = 'admin';

$this->require_admin ();

$page->title = i18n_get ('Install App/Theme');

require_once ('apps/designer/lib/Functions.php');

$form = new Form ('post', $this);

echo $form->handle (function ($form) {
	if (empty ($_POST['github']) && ! is_uploaded_file ($_FILES['zipfile']['tmp_name'])) {
		$form->failed = array ('other');
		return false;
	}

	// handle install from zip or github repo
	info ($_POST);
	info ($_FILES);
});

?>