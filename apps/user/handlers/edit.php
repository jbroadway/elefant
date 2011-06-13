<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
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
		$page->title = i18n_get ('User Saved');
		echo '<p><a href="/user/admin">' . i18n_get ('Continue') . '</a></p>';
		$this->hook ('user/edit', $_POST);
		return;
	}
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $u->error;
} else {
	$u->password = '';
	$u->types = array ('admin', 'member');

	$u->failed = $f->failed;
	$u = $f->merge_values ($u);
	$page->title = i18n_get ('Edit User') . ': ' . $u->name;
	echo $tpl->render ('user/edit', $u);
}

?>