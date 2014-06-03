<?php

/**
 * Admin user list.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'user');

$limit = 20;
$num = isset ($_GET['offset']) ? $_GET['offset'] : 1;
$offset = ($num - 1) * $limit;

$users = User::query ('id, name, email, type, company, title')
	->order ('name asc')
	->fetch_orig ($limit, $offset);
$count = User::query ()->count ();

$page->title = __ ('Members');
echo $tpl->render ('user/admin', array (
	'limit' => $limit,
	'total' => $count,
	'users' => $users,
	'count' => count ($users),
	'url' => '/user/admin?offset=%d'
));

?>