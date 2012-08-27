<?php

/**
 * Default log out handler. You can specify a `redirect` value
 * to send them to after logging out.
 */

if ($appconf['Custom Handlers']['user/logout'] != 'user/logout') {
	if (! $appconf['Custom Handlers']['user/logout']) {
		echo $this->error (404, i18n_get ('Not found'), i18n_get ('The page you requested could not be found.'));
		return;
	}
	echo $this->run ($appconf['Custom Handlers']['user/logout'], $data);
	return;
}

if (! isset ($_GET['redirect'])) {
	$_GET['redirect'] = $appconf['User']['logout_redirect'];
}

if (! Validator::validate ($_GET['redirect'], 'header')) {
	$_GET['redirect'] = '/';
}

Lock::clear ();
echo User::logout ($_GET['redirect']);

?>