<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	$page->title = 'Elefant Admin';
	if (! empty ($_POST['username'])) {
		echo '<p class="notice">' . i18n_get ('Incorrect email or password, please try again.') . '</p>';
	} else {
		echo '<p>' . i18n_get ('Please log in to continue.') . '</p>';
	}
	echo $tpl->render ('admin/index');
	return;
}

header ('Location: /');
exit;

?>