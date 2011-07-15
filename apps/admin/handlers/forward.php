<?php

global $user;
if (User::is_valid () && $user->type == 'admin') {
	$to = isset ($data['to']) ? $data['to'] : $_GET['to'];
	printf (
		'<p>%s:</p><p><a href="%s">%s</a></p>',
		i18n_get ('This page forwards visitors to the following link'),
		$to,
		$to
	);
	return;
}

if (isset ($data['to'])) {
	header ('Location: ' . $data['to']);
} elseif (isset ($_GET['to'])) {
	header ('Location: ' . $_GET['to']);
} else {
	return;
}
exit;

?>