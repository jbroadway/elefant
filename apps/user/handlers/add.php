<?php

/**
 * User add form.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'user');

$f = new Form ('post', 'user/add');
$f->verify_csrf = false;
if ($f->submit ()) {
	if (! User::require_acl ('user/edit_roles')) {
		$_POST['type'] = Appconf::user ('User', 'default_role');
	}
	$_POST['password'] = User::encrypt_pass ($_POST['password']);
	$now = gmdate ('Y-m-d H:i:s');
	$_POST['expires'] = $now;
	$_POST['signed_up'] = $now;
	$_POST['updated'] = $now;
	$_POST['userdata'] = json_encode (array ());
	unset ($_POST['verify_pass']);
	unset ($_POST['_states']);
	unset ($_POST['_countries']);
	$u = new User ($_POST);
	$u->put ();
	Versions::add ($u);
	if (! $u->error) {
		$this->add_notification (__ ('Member added.'));
		$this->hook ('user/add', $_POST);
		$this->redirect ('/user/admin');
	}
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $u->error;
} else {
	$u = new User;
	$u->type = Appconf::user ('User', 'default_role');
	$u->types = User::allowed_roles ();

	$u->failed = $f->failed;
	$u = $f->merge_values ($u);
	$u->_states = user\Data::states ();
	$u->_countries = user\Data::countries ();
	$page->title = __ ('Add Member');
	$page->add_script ('/js/json2.js');
	$page->add_script ('/js/jstorage.js');
	$page->add_script ('/js/jquery.autosave.js');
	echo $tpl->render ('user/add', $u);
}
