<?php

/**
 * Enables a user to update their profile information.
 */

// Check for a custom handler override
$res = $this->override ('user/update');
if ($res) { echo $res; return; }

if (! User::require_login ()) {
	$page->title = __ ('Members');
	echo $this->run ('user/login');
	return;
}

$u = User::$user;

$f = new Form ('post', 'user/update');
if ($f->submit ()) {
	$u->name = $_POST['name'];
	$u->email = $_POST['email'];
	if (! empty ($_POST['password'])) {
		$u->password = User::encrypt_pass ($_POST['password']);
	}
	$u->put ();
	Versions::add ($u);
	if (! $u->error) {
		$page->title = __ ('Profile Updated');
		echo '<p><a href="/user">' . __ ('Continue') . '</a></p>';
		return;
	}
	@error_log ('Error updating profile (#' . $u->id . '): ' . $u->error);
	$page->title = __ ('An Error Occurred');
	echo '<p>' . __ ('Please try again later.') . '</p>';
	echo '<p><a href="/user">' . __ ('Back') . '</a></p>';
} else {
	$u->password = '';
	$u = $f->merge_values ($u);
	$u->failed = $f->failed;
	$page->title = __ ('Update Profile');
	echo $tpl->render ('user/update', $u);
}

?>