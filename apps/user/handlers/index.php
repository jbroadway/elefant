<?php

if ($appconf['Custom Handlers']['user/index'] != 'user/index') {
	echo $this->run ($appconf['Custom Handlers']['user/index'], $data);
	return;
}

if (! $this->params[0]) {
	if (! User::require_login ()) {
		$page->title = i18n_get ('Members');
		echo $this->run ('user/login');
		return;
	}
	global $user;
} else {
	$user = new User ($this->params[0]);
}

$page->title = $user->name;
$data = $user->orig ();
$data->hash = md5 (strtolower (trim ($data->email)));
echo $tpl->render ('user/index', $data);

?>