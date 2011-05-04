<?php

$f = new Form ('post', 'account/signup');
if ($f->submit ()) {
	$date = gmdate ('Y-m-d H:i:s', time ());
	$u = new User (array (
		'email' => $_POST['email'],
		'password' => User::encrypt_pass ($_POST['password']),
		'name' => $_POST['name'],
		'expires' => $date,
		'signed_up' => $date,
		'updated' => $date
	));
	if ($u->put ()) {
		// send a verification/welcome email
		// log them in and send them along
		User::require_login ();
		header ('Location: /account');
		exit;
	}
	$page->title = 'An Error Occurred';
	echo 'We were unable to create your account at this time. Please try again later.';
} else {
	$page->failed = $f->failed;
	$page = $f->merge_values ($page);
	$page->template = 'account/signup';
}

?>