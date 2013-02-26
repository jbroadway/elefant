<?php

/**
 * Default log in handler. You can specify a `redirect` value
 * to send them to after logging in.
 */

if ($appconf['Custom Handlers']['user/login'] != 'user/login') {
	if (! $appconf['Custom Handlers']['user/login']) {
		echo $this->error (404, __ ('Not found'), __ ('The page you requested could not be found.'));
		return;
	}
	echo $this->run ($appconf['Custom Handlers']['user/login'], $data);
	return;
}

if (! $this->internal) {
	$page->title = __ ('Members');
}

if (isset ($_GET['redirect'])) {
	$_POST['redirect'] = $_GET['redirect'];
}

if (! isset ($_POST['redirect'])) {
	$_POST['redirect'] = $_SERVER['REQUEST_URI'];
	if ($_POST['redirect'] == '/user/login') {
		$_POST['redirect'] = '/user';
	}
}

if (! Validator::validate ($_POST['redirect'], 'header')) {
	$_POST['redirect'] = '/user';
}

if (! User::require_login ()) {
	if (! $this->internal && ! empty ($_POST['username'])) {
		echo '<p>' . __ ('Incorrect email or password, please try again.') . '</p>';
	}
	$_POST['signup_handler'] = $appconf['Custom Handlers']['user/signup'];
	echo $tpl->render ('user/login', $_POST);
} elseif (! $this->internal) {
	$this->redirect ($_POST['redirect']);
}

?>