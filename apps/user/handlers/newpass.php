<?php

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

		$page->title = i18n_get ('Password updated');
		echo '<p><a href="/user">' . i18n_get ('Continue') . '</a></p>';
	} else {
		$u = new StdClass;
		$u = $f->merge_values ($u);
		$u->failed = $f->failed;
		$page->add_script ('<script type="text/javascript" src="http://code.jquery.com/jquery-1.5.2.min.js"></script>');
		$page->title = i18n_get ('Choose a new password');
		echo $tpl->render ('user/newpass', $u);
	}
} else {
	$page->title = i18n_get ('Invalid or expired recovery link');
	echo '<p><a href="/">' . i18n_get ('Continue') . '</a></p>';
}

?>