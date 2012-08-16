<?php

/**
 * Admin user list.
 */

$page->layout = 'admin';

$this->require_admin ();

$limit = 20;
$num = isset ($_GET['offset']) ? $_GET['offset'] : 1;
$offset = ($num - 1) * $limit;

$users = User::query ('id, name, email, type')
	->order ('name asc')
	->fetch_orig ($limit, $offset);
$count = User::query ()->count ();

$page->title = i18n_get ('Users');
echo $tpl->render ('user/admin', array (
	'limit' => $limit,
	'total' => $count,
	'users' => $users,
	'count' => count ($users),
	'url' => '/user/admin?offset=%d'
));

?>