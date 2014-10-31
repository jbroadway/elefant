<?php

/**
 * Password recovery form for users who forgot their passwords.
 */

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

	try {
		Mailer::send (array (
			'to' => array ($u->email, $u->name),
			'subject' => __ ('Password recovery'),
			'text' => $tpl->render ('user/email/recover', array (
				'recover' => $data['recover'],
				'email' => $u->email,
				'name' => $u->name
			))
		));
	} catch (Exception $e) {
		@error_log ('Email failed (user/recover): ' . $_POST['email']);
		$page->title = __ ('An Error Occurred');
		echo '<p>' . __ ('Please try again later.') . '</p>';
		echo '<p><a href="/">' . __ ('Back') . '</a></p>';
		return;
	}

	$page->title = __ ('Check your inbox');
	echo '<p>' . __ ('An email has been sent with a link to reset your password.') . '</p>';
} else {
	$u = new StdClass;
	$u->email = '';
	$u = $f->merge_values ($u);
	$u->failed = $f->failed;
	$page->title = __ ('Forgot your password?');
	echo $tpl->render ('user/recover', $u);
}
