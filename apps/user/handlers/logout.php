<?php

/**
 * Default log out handler. You can specify a `redirect` value
 * to send them to after logging out.
 */

// Check for a custom handler override
$res = $this->override ('user/logout');
if ($res) { echo $res; return; }

if (! isset ($_GET['redirect'])) {
	$_GET['redirect'] = Appconf::user ('User', 'logout_redirect');
}

if (! Validator::validate ($_GET['redirect'], 'header')) {
	$_GET['redirect'] = '/';
}

Lock::clear ();
echo User::logout ($_GET['redirect']);

?>