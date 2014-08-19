<?php

/**
 * Adds a new user for the user chooser's new user form.
 */

$this->require_acl ('admin', 'user');

$page->layout = false;

header ('Content-Type: application/json');

$f = new Form ('post', 'user/add');
$f->verify_csrf = false;
if (! $f->submit ()) {
	echo json_encode (array (
		'success' => false,
		'error' => __ ('Form validation failed. Please review and try again.')
	));
	return;
}

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
	$this->add_notification (__ ('Member added.'));
	$this->hook ('user/add', $_POST);
	echo json_encode (array (
		'success' => true,
		'data' => array (
			'id' => $u->id,
			'name' => $u->name,
			'email' => $u->email
		)
	));
	return;
}

echo json_encode (array (
	'success' => false,
	'error' => __ ('An Error Occurred') . ': ' . $u->error
));

?>