<?php

/**
 * Default user profile view. If not logged in, will show a log in form.
 */

// Check for a custom handler override
$res = $this->override ('user/index');
if ($res) { echo $res; return; }

if (! $this->params[0]) {
	if (! User::require_login ()) {
		$page->title = __ ('Members');
		echo $this->run ('user/login');
		return;
	}
	$user = User::$user;
	$page->title = Template::sanitize ($user->name);
	$data = $user->orig ();
	$data->is_current = true;
} else {
	$user = new User ($this->params[0]);
	$page->title = Template::sanitize ($user->name);
	$data = $user->orig ();
	$data->is_current = (User::is_valid () && $this->params[0] === User::$user->id) ? true : false;
}

echo $tpl->render ('user/index', $data);

?>