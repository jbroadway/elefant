<?php

/**
 * Outputs an admin login form at /admin if the
 * user isn't logged in as an admin. If they are,
 * it simply forwards to / where they should see
 * the admin toolbar and edit buttons.
 */

$page->id = 'admin';
$page->layout = 'admin';

if (isset ($_GET['redirect'])) {
	$_POST['redirect'] = $_GET['redirect'];
}

if (! isset ($_POST['redirect'])
	|| empty ($_POST['redirect'])
	|| ! Validator::validate ($_POST['redirect'], 'header')
) {
	$_POST['redirect'] = $appconf['General']['login_redirect'];
}

$redir = parse_url ($_POST['redirect']);
if ($redir === false || $_POST['redirect'] !== $redir['path'] && $_POST['redirect'] !== $redir['path'] . '?' . $redir['query']) {
	$_POST['redirect'] = $appconf['General']['login_redirect'];
}

if (! User::require_admin ()) {
	$page->title = sprintf (
		'<img src="%s" alt="%s" style="margin-left: -7px" />',
		Product::logo_login (),
		Product::name ()
	);
	$page->window_title = __ ('Please log in to continue.');
	if (! empty ($_POST['username'])) {
		echo '<p>' . __ ('Incorrect email or password, please try again.') . '</p>';
	} else {
		echo '<p>' . __ ('Please log in to continue.') . '</p>';
	}
	echo $tpl->render ('admin/index');
	return;
}

$this->redirect ($_POST['redirect']);
