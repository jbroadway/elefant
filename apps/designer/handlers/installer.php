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
	if (! empty ($_POST['github'])) {
		// Import from Github
		$installer = new GithubInstaller;
		if (! $installer->install ($_POST['github'])) {
			$form->failed = array ('github-install');
			return false;
		}

		// App/theme successfully installed
	} elseif (is_uploaded_file ($_FILES['zipfile']['tmp_name'])) {
		// Import from Zip
		$installer = new ZipInstaller;
		$res = $installer->install ($_FILES['zipfile']);
		if (! $res) {
			$form->failed = array ('zip-install');
			return false;
		}

		// Zip successfully installed
		global $page, $tpl;
		$page->title = i18n_get ('Install completed');
		echo $tpl->render ('designer/installed', $res);
	} else {
		$form->failed = array ('other');
		return false;
	}
});

?>