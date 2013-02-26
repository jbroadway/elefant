<?php

/**
 * Form to reset your password, accessed through a link from an email.
 */

$verified = false;

$u = User::query ()
	->where ('email', $_GET['email'])
	->single ();

$data = $u->userdata;

if ($data['recover'] == $_GET['recover'] && $data['recover_expires'] > time () + 60) {
	$f = new Form ('post', 'user/newpass');
	if ($f->submit ()) {
		$u->password = User::encrypt_pass ($_POST['password']);
		unset ($data['recover']);
		unset ($data['recover_expires']);
		$u->userdata = $data;
		$u->put ();

		$_POST['username'] = $u->email;
		User::require_login ();

		$page->title = __ ('Password updated');
		echo '<p><a href="/user">' . __ ('Continue') . '</a></p>';
	} else {
		$u = new StdClass;
		$u = $f->merge_values ($u);
		$u->failed = $f->failed;
		$page->title = __ ('Choose a new password');
		echo $tpl->render ('user/newpass', $u);
	}
} else {
	$page->title = __ ('Invalid or expired recovery link');
	echo '<p><a href="/">' . __ ('Continue') . '</a></p>';
}

?>