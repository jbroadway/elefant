<?php

/**
 * New user form for registering social login users.
 */

if (! $appconf['Custom Handlers']['user/signup']) {
	echo $this->error (404, __ ('Not found'), __ ('The page you requested could not be found.'));
	return;
}

// Check for a custom handler override
$res = $this->override ('user/login/newuser');
if ($res) { echo $res; return; }

$f = new Form ('post', 'user/login/newuser');
if ($f->submit ()) {
	$date = gmdate ('Y-m-d H:i:s');
	$u = new User (array (
		'name' => $_POST['name'],
		'email' => $_POST['email'],
		'password' => User::encrypt_pass ($_POST['password']),
		'expires' => $date,
		'type' => Appconf::user ('User', 'default_role'),
		'signed_up' => $date,
		'updated' => $date,
		'userdata' => json_encode (array ()),
		'about' => ''
	));
	$u->put ();
	Versions::add ($u);
	if (! $u->error) {
		$oid = new User_OpenID (array (
			'token' => $_POST['token'],
			'user_id' => $u->id
		));
		$oid->put ();

		$_POST['username'] = $_POST['email'];
		User::require_login ();
		$this->redirect ($_POST['redirect']);
	}
	// TODO: already have an account
	@error_log ('Error creating profile: ' . $u->error);
	$page->title = 'An Error Occurred';
	echo '<p>Please try again later.</p>';
	echo '<p><a href="/">' . __ ('Back') . '</a></p>';
} else {
	$u = new User;
	$u = $f->merge_values ($u);
	$u = $u->orig ();
	$u->failed = $f->failed;
	$page->title = __ ('New User');
	echo $tpl->render ('user/login/newuser', $u);
}
