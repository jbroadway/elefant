<?php

/**
 * Install an app or theme from a zip or Github repo.
 */

$page->layout = 'admin';

$this->require_admin ();

$page->title = i18n_get ('Install App/Theme');

$form = new Form ('post', $this);

echo $form->handle (function ($form) {
	// handle install from zip or github repo
	info ($_POST);
});

?>