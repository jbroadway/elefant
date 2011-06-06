<?php

if (! $this->internal) {
	$page->title = i18n_get ('Members');
}

if (! isset ($_POST['redirect'])) {
	$_POST['redirect'] = $_SERVER['REQUEST_URI'];
}

if (! User::require_login ()) {
	if (! $this->internal && ! empty ($_POST['username'])) {
		echo '<p class="notice">' . i18n_get ('Incorrect email or password, please try again.') . '</p>';
	}
	echo $tpl->render ('user/login', $_POST);
} elseif (! $this->internal) {
	header ('Location: ' . $_POST['redirect']);
	exit;
}

?>