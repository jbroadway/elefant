<?php

$f = new Form ('post', 'user/recover');
if ($f->submit ()) {
	$u = User::query ()
		->where ('email', $_POST['email'])
		->single ();

	$data = $u->userdata;
	$data['recover'] = md5 (uniqid (mt_rand (), 1));
	$data['recover_expires'] = time () + 7200;
	$u->userdata = $data;
	$u->put ();

	if (! @mail (
		$u->name . ' <' . $u->email . '>',
		'Password recovery',
		$tpl->render ('user/email/recover', array (
			'recover' => $data['recover'],
			'email' => $u->email,
			'name' => $u->name
		)),
		'From: ' . $conf['General']['site_name'] . ' <' . $conf['General']['email_from'] . '>'
	)) {
		@error_log ('Email failed (user/recover): ' . $_POST['email']);
		$page->title = i18n_get ('An Error Occurred');
		echo '<p>Please try again later.</p>';
		echo '<p><a href="/">' . i18n_get ('Back') . '</a></p>';
		return;
	}

	$page->title = i18n_get ('Check your inbox');
	echo '<p>' . i18n_get ('An email has been sent with a link to reset your password.') . '</p>';
} else {
	$u = new StdClass;
	$u->email = '';
	$u = $f->merge_values ($u);
	$u->failed = $f->failed;
	$page->add_script ('<script type="text/javascript" src="http://code.jquery.com/jquery-1.5.2.min.js"></script>');
	$page->title = i18n_get ('Forgot your password?');
	echo $tpl->render ('user/recover', $u);
}

?>