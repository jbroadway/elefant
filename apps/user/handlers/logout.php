<?php

if ($appconf['Custom Handlers']['user/logout'] != 'user/logout') {
	echo $this->run ($appconf['Custom Handlers']['user/logout'], $data);
	return;
}

if (! isset ($_GET['redirect'])) {
	$_GET['redirect'] = $appconf['User']['logout_redirect'];
}

Lock::clear ();
echo User::logout ($_GET['redirect']);

?>