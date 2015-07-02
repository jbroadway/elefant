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

$redir = parse_url ($_GET['redirect']);
if ($redir === false || $_GET['redirect'] !== $redir['path'] && $_GET['redirect'] !== $redir['path'] . '?' . $redir['query']) {
	$_GET['redirect'] = '/';
}

Lock::clear ();
echo User::logout ($_GET['redirect']);
