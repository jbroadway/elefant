<?php

/**
 * Default user profile view. If not logged in, will show a log in form.
 */

if ($appconf['Custom Handlers']['user/index'] != 'user/index') {
	if (! $appconf['Custom Handlers']['user/index']) {
		echo $this->error (404, __ ('Not found'), __ ('The page you requested could not be found.'));
		return;
	}
	echo $this->run ($appconf['Custom Handlers']['user/index'], $data);
	return;
}

if (! $this->params[0]) {
	if (! User::require_login ()) {
		$page->title = __ ('Members');
		echo $this->run ('user/login');
		return;
	}
	$user = User::$user;
	$page->title = $user->name;
	$data = $user->orig ();
	$data->is_current = true;
} else {
	$user = new User ($this->params[0]);
	$page->title = $user->name;
	$data = $user->orig ();
	$data->is_current = (User::is_valid () && $this->params[0] === User::$user->id) ? true : false;
}

$data->hash = md5 (strtolower (trim ($data->email)));
echo $tpl->render ('user/index', $data);

?>