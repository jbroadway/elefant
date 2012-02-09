<?php

/**
 * Install an app or theme from a zip or Github repo.
 */

$page->layout = 'admin';

$this->require_admin ();

$page->title = i18n_get ('Install App/Theme');

require_once ('apps/designer/lib/Functions.php');

$form = new Form ('post', $this);
$page->installer_error = false;

echo $form->handle (function ($form) {
	global $page, $tpl;

	if (! empty ($_POST['github'])) {
		if (github_is_zip ($_POST['github'])) {
			ZipInstaller::clean ();

			// Retrieve zip file
			$info = ZipInstaller::fetch ($_POST['github']);
			if (! $info) {
				ZipInstaller::clean ();
				$form->failed = array ('zip-install');
				$page->installer_error = ZipInstaller::$error;
				return false;
			}

			// Import from Zip
			$res = ZipInstaller::install ($info);
			if (! $res) {
				ZipInstaller::clean ();
				$form->failed = array ('zip-install');
				$page->installer_error = ZipInstaller::$error;
				return false;
			}

			// Zip successfully installed
			ZipInstaller::clean ();
			$page->title = i18n_get ('Install completed');
			echo $tpl->render ('designer/installed', $res);
		} else {
			// Import from Github
			$res = GithubInstaller::install ($_POST['github']);
			if (! $res) {
				$form->failed = array ('github-install');
				$page->installer_error = GithubInstaller::$error;
				return false;
			}
	
			// App/theme successfully installed
			$page->title = i18n_get ('Install completed');
			echo $tpl->render ('designer/installed', $res);
		}
	} elseif (is_uploaded_file ($_FILES['zipfile']['tmp_name'])) {
		ZipInstaller::clean ();

		// Import from Zip
		$res = ZipInstaller::install ($_FILES['zipfile']);
		if (! $res) {
			ZipInstaller::clean ();
			$form->failed = array ('zip-install');
			$page->installer_error = ZipInstaller::$error;
			return false;
		}

		// Zip successfully installed
		ZipInstaller::clean ();
		$page->title = i18n_get ('Install completed');
		echo $tpl->render ('designer/installed', $res);
	} else {
		$form->failed = array ('other');
		return false;
	}
});

?>