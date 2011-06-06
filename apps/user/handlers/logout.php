<?php

if (! isset ($_GET['redirect'])) {
	$_GET['redirect'] = $appconf['User']['logout_redirect'];
}

echo User::logout ($_GET['redirect']);

?>