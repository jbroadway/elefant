<?php

/**
 * Admin user list.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$limit = 20;
$_GET['offset'] = (isset ($_GET['offset'])) ? $_GET['offset'] : 0;

$users = User::query ('id, name, email, type')
	->order ('name asc')
	->fetch_orig ($limit, $_GET['offset']);
$count = User::query ()->count ();

$page->title = i18n_get ('Users');
echo $tpl->render ('user/admin', array (
	'users' => $users,
	'count' => $count,
	'offset' => $_GET['offset'],
	'more' => ($count > $_GET['offset'] + $limit) ? true : false,
	'prev' => $_GET['offset'] - $limit,
	'next' => $_GET['offset'] + $limit
));

?>