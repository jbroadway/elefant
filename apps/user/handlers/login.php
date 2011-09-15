<?php

if ($appconf['Custom Handlers']['user/login'] != 'user/login') {
	echo $this->run ($appconf['Custom Handlers']['user/login'], $data);
	return;
}

if (! $this->internal) {
	$page->title = i18n_get ('Members');
}

if (! isset ($_POST['redirect'])) {
	$_POST['redirect'] = $_SERVER['REQUEST_URI'];
	if ($_POST['redirect'] == '/user/login') {
		$_POST['redirect'] = '/user';
	}
}

if (! User::require_login ()) {
	if (! $this->internal && ! empty ($_POST['username'])) {
		echo '<p>' . i18n_get ('Incorrect email or password, please try again.') . '</p>';
	}
	echo $tpl->render ('user/login', $_POST);
} elseif (! $this->internal) {
	header ('Location: ' . $_POST['redirect']);
	exit;
}

?>