<?php

/**
 * User edit form.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'user');

$u = new User ($_GET['id']);

$f = new Form ('post', 'user/edit');
$f->verify_csrf = false;
if ($f->submit ()) {
	$u->name = $_POST['name'];
	$u->email = $_POST['email'];
	if (User::require_acl ('user/edit_roles')) {
		$u->type = $_POST['type'];
	}
	if (! empty ($_POST['password'])) {
		$u->password = User::encrypt_pass ($_POST['password']);
	}
	$u->photo = $_POST['photo'];
	$u->about = $_POST['about'];
	$u->phone = $_POST['phone'];
	$u->fax = $_POST['fax'];
	$u->address = $_POST['address'];
	$u->address2 = $_POST['address2'];
	$u->city = $_POST['city'];
	$u->state = $_POST['state'];
	$u->country = $_POST['country'];
	$u->zip = $_POST['zip'];
	$u->title = $_POST['title'];
	$u->company = $_POST['company'];
	$u->website = $_POST['website'];
	$u->update_extended ();
	$u->put ();
	Versions::add ($u);
	if (! $u->error) {
		$this->add_notification (__ ('Member saved.'));
		$this->hook ('user/edit', $_POST);
		$this->redirect ('/user/admin');
	}
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $u->error;
} else {
	$u->password = '';
	$u->types = User::allowed_roles ();

	$u->failed = $f->failed;
	$u = $f->merge_values ($u);
	$u->_states = user\Data::states ();
	$u->_countries = user\Data::countries ();
	$page->title = __ ('Edit Member') . ': ' . $u->name;
	$page->add_script ('/js/json2.js');
	$page->add_script ('/js/jstorage.js');
	$page->add_script ('/js/jquery.autosave.js');
	echo $tpl->render ('user/edit', $u);
}
