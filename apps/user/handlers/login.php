<?php

/**
 * Default log in handler. You can specify a `redirect` value
 * to send them to after logging in.
 */

if ($appconf['Custom Handlers']['user/login'] != 'user/login') {
	if (! $appconf['Custom Handlers']['user/login']) {
		echo $this->error (404, i18n_get ('Not found'), i18n_get ('The page you requested could not be found.'));
		return;
	}
	echo $this->run ($appconf['Custom Handlers']['user/login'], $data);
	return;
}

if (! $this->internal) {
	$page->title = i18n_get ('Members');
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

if (! Form::verify_value ($_POST['redirect'], 'header')) {
	$_POST['redirect'] = '/user';
}

if (! User::require_login ()) {
	if (! $this->internal && ! empty ($_POST['username'])) {
		echo '<p>' . i18n_get ('Incorrect email or password, please try again.') . '</p>';
	}
	$_POST['signup_handler'] = $appconf['Custom Handlers']['user/signup'];
	echo $tpl->render ('user/login', $_POST);
} elseif (! $this->internal) {
	$this->redirect ($_POST['redirect']);
}

?>