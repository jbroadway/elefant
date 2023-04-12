<?php

use PragmaRX\Google2FAQRCode\Google2FA;

if (! User::is_session_valid ()) {
	$page->title = __ ('Members');
	echo $this->run ('user/login');
	return;
}

$u = User::$user;

$form = new Form ('post', $this);

$page->title = __ ('2-Factor Authentication');

// 2fa
$global_2fa = Appconf::user ('User', '2fa');
$g2fa = new Google2FA (
	new \PragmaRX\Google2FAQRCode\QRCode\Chillerlan ()
);
$secret = '';
if (! isset ($u->userdata['2fa_secret'])) {
	$secret = $g2fa->generateSecretKey (32);
	$userdata = $u->userdata;
	$userdata['2fa_secret'] = $secret;
	$u->userdata = $userdata;

	if (! $u->put ()) {
		echo '<p>' . __ ('Unable to generate 2-factor secret code. Please try again later.') . '</p>';
		echo '<p><a href="/user">' . __ ('Continue') . '</a></p>';
		return;
	}
} else {
	$secret = $u->userdata['2fa_secret'];
}

$form->data = [
	'qrcode_url' => $g2fa->getQRCodeInline (
		Appconf::admin ('Site Settings', 'site_name'),
		$u->email,
		$secret
	),
	'global_2fa' => Appconf::user ('User', '2fa')
];

echo $form->handle (function ($form) use ($u, $page, $g2fa, $secret) {
	if ($g2fa->verifyKey ($secret, $_POST['code'])) {
		$userdata = $u->userdata;
		$userdata['2fa'] = 'on';
		$u->userdata = $userdata;

		if (! $u->put ()) {
			echo '<p>' . __ ('Unable to save 2-factor authentication settings. Please try again later.') . '</p>';
			echo '<p><a href="/user">' . __ ('Continue') . '</a></p>';
			return;
		}

		User::verify_2fa ();

		$page->title = __ ('2-Factor Authentication Verified');
		echo '<p><a href="/user">' . __ ('Continue') . '</a></p>';
		return;
	} else {
		$form->failed[] = 'code';
		return false;
	}
});
