<?php

/**
 * Admin user list.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'user');

$limit = 20;
$num = isset ($_GET['offset']) ? $_GET['offset'] : 1;
$offset = ($num - 1) * $limit;
$q = isset ($_GET['q']) ? $_GET['q'] : '';
$q_fields = array ('name', 'email', 'company', 'city', 'state', 'address', 'type', 'phone', 'title');
$q_exact = array ('company', 'type');
$url = ! empty ($q)
	? '/user/admin?q=' . urlencode ($q) . '&offset=%d'
	: '/user/admin?offset=%d';

$users = User::query ('id, name, email, type, company, title, phone')
	->where_search ($q, $q_fields, $q_exact)
	->order ('name asc')
	->fetch_orig ($limit, $offset);

$count = User::query ()
	->where_search ($q, $q_fields, $q_exact)
	->count ();

$page->title = __ ('Accounts');
echo $tpl->render ('user/admin', array (
	'limit' => $limit,
	'total' => $count,
	'users' => $users,
	'count' => count ($users),
	'url' => $url,
	'q' => $q
));
