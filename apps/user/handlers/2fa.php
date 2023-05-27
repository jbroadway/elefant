<?php

/**
 * 2FA verification handler.
 */

use PragmaRX\Google2FAQRCode\Google2FA;

if (! User::is_session_valid ()) {
	$page->title = __ ('Members');
	echo $this->run ('user/login');
	return;
}

$u = User::$user;

$page->title = __ ('2-Factor Authentication');

$form = new Form ('post', $this);

// 2fa
$global_2fa = Appconf::user ('User', '2fa');
$g2fa = new Google2FA ();

if (! isset ($u->userdata['2fa_secret']) || ! isset ($u->userdata['2fa']) || $u->userdata['2fa'] != 'on') {
	$this->redirect ('/user/update2fa');
}

$secret = $u->userdata['2fa_secret'];

if (! isset ($_GET['redirect'])) {
	$_GET['redirect'] = '/user';
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
