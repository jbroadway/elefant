<?php

/**
 * Default log in handler.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('user/login');
 *
 * In a template, call it like this:
 *
 *     {! user/login !}
 *
 * Parameters:
 *
 * - `redirect` - A URL to redirect to upon successful login (default = `/user`)
 *
 * Also available in the dynamic objects menu as "User: Login".
 */

// Check for a custom handler override
$res = $this->override ('user/login');
if ($res) { echo $res; return; }

if (! $this->internal) {
	$page->title = __ ('Members');
} elseif (isset ($data['redirect'])) {
	$_POST['redirect'] = $data['redirect'];
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

$redir = parse_url ($_POST['redirect']);
if ($redir === false || $_POST['redirect'] !== $redir['path'] && $_POST['redirect'] !== $redir['path'] . '?' . $redir['query']) {
	$_POST['redirect'] = '/user';
}

if (! User::require_login ()) {
	
	if (! $this->internal && User::require_2fa ()) {
		$this->redirect ('/user/2fa?redirect=' . $_POST['redirect']);
	}

	if (! $this->internal && ! empty ($_POST['username'])) {
		echo '<p>' . __ ('Incorrect email or password, please try again.') . '</p>';
	}
	$_POST['signup_handler'] = Appconf::user ('Custom Handlers', 'user/signup');
	echo $tpl->render ('user/login', $_POST);
} elseif (! $this->internal) {
	$this->redirect ($_POST['redirect']);
}
