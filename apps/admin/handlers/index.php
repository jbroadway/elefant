<?php

/**
 * Outputs an admin login form at /admin if the
 * user isn't logged in as an admin. If they are,
 * it simply forwards to / where they should see
 * the admin toolbar and edit buttons.
 */

$page->layout = 'admin';

if (isset ($_GET['redirect'])) {
	$_POST['redirect'] = $_GET['redirect'];
}

if (! isset ($_POST['redirect']) || empty ($_POST['redirect'])) {
	$_POST['redirect'] = '/';
}

if (! Validator::validate ($_POST['redirect'], 'header')) {
	$_POST['redirect'] = '/';
}

if (! User::require_admin ()) {
	$page->title = sprintf (
		'<img src="%s" alt="%s" style="margin-left: -7px" />',
		Product::logo_login (),
		Product::name ()
	);
	$page->window_title = i18n_get ('Please log in to continue.');
	if (! empty ($_POST['username'])) {
		echo '<p>' . i18n_get ('Incorrect email or password, please try again.') . '</p>';
	} else {
		echo '<p>' . i18n_get ('Please log in to continue.') . '</p>';
	}
	echo $tpl->render ('admin/index');
	return;
}

$this->redirect ($_POST['redirect']);

?>