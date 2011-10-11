<?php

/**
 * Enables a user to update their profile information.
 */

if ($appconf['Custom Handlers']['user/update'] != 'user/update') {
	if (! $appconf['Custom Handlers']['user/update']) {
		echo $this->error (404, i18n_get ('Not found'), i18n_get ('The page you requested could not be found.'));
		return;
	}
	echo $this->run ($appconf['Custom Handlers']['user/update'], $data);
	return;
}

if (! User::require_login ()) {
	$page->title = i18n_get ('Members');
	echo $this->run ('user/login');
	return;
}

global $user;

$u = $user;

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
		$page->title = i18n_get ('Profile Updated');
		echo '<p><a href="/user">' . i18n_get ('Continue') . '</a></p>';
		return;
	}
	@error_log ('Error updating profile (#' . $u->id . '): ' . $u->error);
	$page->title = i18n_get ('An Error Occurred');
	echo '<p>' . i18n_get ('Please try again later.') . '</p>';
	echo '<p><a href="/user">' . i18n_get ('Back') . '</a></p>';
} else {
	$u->password = '';
	$u = $f->merge_values ($u);
	$u->failed = $f->failed;
	$page->title = i18n_get ('Update Profile');
	echo $tpl->render ('user/update', $u);
}

?>