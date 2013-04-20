<?php

/**
 * User add form.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$f = new Form ('post', 'user/add');
$f->verify_csrf = false;
if ($f->submit ()) {
	$_POST['password'] = User::encrypt_pass ($_POST['password']);
	$now = gmdate ('Y-m-d H:i:s');
	$_POST['expires'] = $now;
	$_POST['signed_up'] = $now;
	$_POST['updated'] = $now;
	$_POST['userdata'] = json_encode (array ());
	unset ($_POST['verify_pass']);
	$u = new User ($_POST);
	$u->put ();
	Versions::add ($u);
	if (! $u->error) {
		$this->add_notification (__ ('User added.'));
		$this->hook ('user/add', $_POST);
		$this->redirect ('/user/admin');
	}
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $u->error;
} else {
	$u = new User;
	$u->type = 'admin';
	$u->types = array_keys (User::acl ()->rules);

	$u->failed = $f->failed;
	$u = $f->merge_values ($u);
	$page->title = __ ('Add User');
	echo $tpl->render ('user/add', $u);
}

?>