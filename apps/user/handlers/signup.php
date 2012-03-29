<?php

/**
 * Default user sign up form.
 */

if ($appconf['Custom Handlers']['user/signup'] != 'user/signup') {
	if (! $appconf['Custom Handlers']['user/signup']) {
		echo $this->error (404, i18n_get ('Not found'), i18n_get ('The page you requested could not be found.'));
		return;
	}
	echo $this->run ($appconf['Custom Handlers']['user/signup'], $data);
	return;
}

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
		try {
			Mailer::send (array (
				'to' => array ($_POST['email'], $_POST['name']),
				'subject' => i18n_get ('Please confirm your email address'),
				'text' => $tpl->render ('user/email/verification', array (
					'verifier' => $verifier,
					'email' => $_POST['email'],
					'name' => $_POST['name']
				))
			));
		} catch (Exception $e) {
			@error_log ('Email failed (user/signup): ' . $u->error);
			$u->userdata = array ();
			$u->put ();
		}

		$_POST['username'] = $_POST['email'];
		User::require_login ();
		$this->redirect ('/user');
	}
	@error_log ('Error creating profile: ' . $u->error);
	$page->title = i18n_get ('An Error Occurred');
	echo '<p>' . i18n_get ('Please try again later.') . '</p>';
	echo '<p><a href="/">' . i18n_get ('Back') . '</a></p>';
} else {
	$u = new User;
	$u = $f->merge_values ($u);
	$u->failed = $f->failed;
	$page->title = i18n_get ('Sign Up');
	echo $tpl->render ('user/signup', $u);
}

?>