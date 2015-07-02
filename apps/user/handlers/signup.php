<?php

/**
 * Default user sign up form. You can specify a `redirect` value
 * to send them to after logging in if you embed the signup into
 * a page via:
 *
 *     echo $this->run ('user/signup', array ('redirect' => '/welcome'));
 *
 * Or in a template via:
 *
 *     {! user/signup?redirect=/welcome !}
 */

// If they're already logged in, redirect them
if (User::require_login ()) {
	$this->redirect ('/user');
}

// Check for a custom handler override
$res = $this->override ('user/signup');
if ($res) { echo $res; return; }

$f = new Form ('post', 'user/signup');
if ($f->submit ()) {
	$date = gmdate ('Y-m-d H:i:s');
	$verifier = md5 (uniqid (mt_rand (), 1));
	$u = new User (array (
		'name' => $_POST['name'],
		'email' => $_POST['email'],
		'password' => User::encrypt_pass ($_POST['password']),
		'expires' => $date,
		'type' => Appconf::user ('User', 'default_role'),
		'signed_up' => $date,
		'updated' => $date,
		'userdata' => json_encode (array ('verifier' => $verifier)),
		'about' => ''
	));
	$u->put ();
	Versions::add ($u);
	if (! $u->error) {
		try {
			Mailer::send (array (
				'to' => array ($_POST['email'], $_POST['name']),
				'subject' => __ ('Please confirm your email address'),
				'text' => $tpl->render ('user/email/verification', array (
					'verifier' => $verifier,
					'email' => $_POST['email'],
					'name' => $_POST['name']
				))
			));
		} catch (Exception $e) {
			@error_log ('Email failed (user/signup): ' . $u->error);
			$userdata = json_decode ($u->data['userdata'], true);
			unset ($userdata['verifier']);
			$u->data['userdata'] = json_encode ($userdata);
			$u->put ();
		}

		$_POST['username'] = $_POST['email'];
		User::require_login ();
		$redirect = isset ($data['redirect']) ? $data['redirect'] : '/user';
		$this->redirect ($redirect);
	}
	@error_log ('Error creating profile: ' . $u->error);
	$page->title = __ ('An Error Occurred');
	echo '<p>' . __ ('Please try again later.') . '</p>';
	echo '<p><a href="/">' . __ ('Back') . '</a></p>';
} else {
	$u = new User;
	$u = $f->merge_values ($u);
	$u->failed = $f->failed;
	if (! $this->internal) {
		$page->title = __ ('Sign Up');
	}
	echo $tpl->render ('user/signup', $u);
}
