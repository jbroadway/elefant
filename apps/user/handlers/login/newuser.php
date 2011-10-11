<?php

/**
 * New user form for registering social login users.
 */

$f = new Form ('post', 'user/login/newuser');
if ($f->submit ()) {
	$date = gmdate ('Y-m-d H:i:s');
	$u = new User (array (
		'name' => $_POST['name'],
		'email' => $_POST['email'],
		'password' => User::encrypt_pass ($_POST['password']),
		'expires' => $date,
		'type' => 'member',
		'signed_up' => $date,
		'updated' => $date,
		'userdata' => json_encode (array ())
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
	echo '<p><a href="/">' . i18n_get ('Back') . '</a></p>';
} else {
	$u = new User;
	$u = $f->merge_values ($u);
	$u->failed = $f->failed;
	$page->title = i18n_get ('New User');
	echo $tpl->render ('user/login/newuser', $u);
}

?>