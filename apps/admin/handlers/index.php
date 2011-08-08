<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	$page->title = '<img src="/apps/admin/css/admin/elefant_logo_login.png" alt="Elefant CMS" style="margin-left: -7px" />';
	if (! empty ($_POST['username'])) {
		echo '<p>' . i18n_get ('Incorrect email or password, please try again.') . '</p>';
	} else {
		echo '<p>' . i18n_get ('Please log in to continue.') . '</p>';
	}
	echo $tpl->render ('admin/index');
	return;
}

header ('Location: /');
exit;

?>