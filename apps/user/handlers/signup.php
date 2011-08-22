<?php

$f = new Form ('post', 'user/signup');
if ($f->submit ()) {
	$date = gmdate ('Y-m-d H:i:s');
	$verifier = md5 (uniqid (mt_rand (), 1));
	$u = new User (array (
		'name' => $_POST['name'],
		'email' => $_POST['email'],
		'password' => User::encrypt_pass ($_POST['password']),
		'expires' => $date,
		'type' => 'member',
		'signed_up' => $date,
		'updated' => $date,
		'userdata' => json_encode (array ('verifier' => $verifier))
	));
	$u->put ();
	Versions::add ($u);
	if (! $u->error) {
		if (! @mail (
			$_POST['name'] . ' <' . $_POST['email'] . '>',
			'Email verification',
			$tpl->render ('user/email/verification', array (
				'verifier' => $verifier,
				'email' => $_POST['email'],
				'name' => $_POST['name']
			)),
			'From: ' . conf ('General', 'site_name') . ' <' . conf ('General', 'email_from') . '>'
		)) {
			// undo verification since email failed
			// here we assume they're okay
			@error_log ('Email failed (user/signup): ' . $u->error);
			$u->userdata = array ();
			$u->put ();
		}

		$_POST['username'] = $_POST['email'];
		User::require_login ();
		header ('Location: /user');
		exit;
	}
	@error_log ('Error creating profile: ' . $u->error);
	$page->title = 'An Error Occurred';
	echo '<p>Please try again later.</p>';
	echo '<p><a href="/">' . i18n_get ('Back') . '</a></p>';
} else {
	$u = new User;
	$u = $f->merge_values ($u);
	$u->failed = $f->failed;
	$page->title = i18n_get ('Sign Up');
	echo $tpl->render ('user/signup', $u);
}

?>