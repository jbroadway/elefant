<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$u = new User ($_GET['id']);

$f = new Form ('post', 'user/edit');
if ($f->submit ()) {
	$u->name = $_POST['name'];
	$u->email = $_POST['email'];
	if (! empty ($_POST['password'])) {
		$u->password = User::encrypt_pass ($_POST['password']);
	}
	$u->put ();
	Versions::add ($u);
	if (! $u->error) {
		$this->add_notification (i18n_get ('User saved.'));
		$this->hook ('user/edit', $_POST);
		$this->redirect ('/user/admin');
	}
	$page->title = i18n_get ('An Error Occurred');
	echo i18n_get ('Error Message') . ': ' . $u->error;
} else {
	$u->password = '';
	$u->types = array ('admin', 'member');

	$u->failed = $f->failed;
	$u = $f->merge_values ($u);
	$page->title = i18n_get ('Edit User') . ': ' . $u->name;
	echo $tpl->render ('user/edit', $u);
}

?>