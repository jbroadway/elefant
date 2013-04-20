<?php

/**
 * User edit form.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$u = new User ($_GET['id']);

$f = new Form ('post', 'user/edit');
$f->verify_csrf = false;
if ($f->submit ()) {
	$u->name = $_POST['name'];
	$u->email = $_POST['email'];
	$u->type = $_POST['type'];
	if (! empty ($_POST['password'])) {
		$u->password = User::encrypt_pass ($_POST['password']);
	}
	$u->update_extended ();
	$u->put ();
	Versions::add ($u);
	if (! $u->error) {
		$this->add_notification (__ ('User saved.'));
		$this->hook ('user/edit', $_POST);
		$this->redirect ('/user/admin');
	}
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $u->error;
} else {
	$u->password = '';
	$u->types = array_keys (User::acl ()->rules);

	$u->failed = $f->failed;
	$u = $f->merge_values ($u);
	$page->title = __ ('Edit User') . ': ' . $u->name;
	echo $tpl->render ('user/edit', $u);
}

?>