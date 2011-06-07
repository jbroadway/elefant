<?php

if (! $this->params[0]) {
	User::require_login ();
	global $user;
	$u = $user;
} else {
	$u = new User ($this->params[0]);
}

$page->title = $u->name;
$data = $u->orig ();
$data->hash = md5 (strtolower (trim ($data->email)));
echo $tpl->render ('user/profile', $data);

?>