<?php

/**
 * 2FA verification handler.
 */

$page->id = 'admin';
$page->layout = 'admin';

use PragmaRX\Google2FAQRCode\Google2FA;

if (! User::is_session_valid ()) {
	$page->title = __ ('Members');
	echo $this->run ('user/login');
	return;
}

$u = User::$user;

$page->window_title = __ ('2-Factor Authentication');

$page->title = sprintf (
	'<img src="%s" alt="%s" style="margin-left: -7px" />',
	Product::logo_login (),
	Product::name ()
);

$form = new Form ('post', $this);

// 2fa
$global_2fa = Appconf::user ('User', '2fa');
$g2fa = new Google2FA ();

if (! isset ($u->userdata['2fa_secret'])) {
	echo '<p>' . __ ('2-factor authentication is not set up for this account.') . '</p>';
	echo '<p><a href="/">' . __ ('Continue') . '</a></p>';
	return;
}

$secret = $u->userdata['2fa_secret'];

if (! isset ($_GET['redirect'])) {
	$_GET['redirect'] = '/';
}

echo $form->handle (function ($form) use ($u, $page, $g2fa, $secret) {
	if ($g2fa->verifyKey ($secret, $_POST['code'])) {
		User::verify_2fa ();
		$form->controller->redirect ($_GET['redirect']);
	} else {
		$form->failed[] = 'invalid';
		return false;
	}
});
