<?php

/**
 * Show a list of all pages for admins.
 */

$this->require_acl ('admin', 'admin/pages');

$page->layout = 'admin';
$page->title = __ ('All Pages');

$limit = 20;
$num = isset ($_GET['offset']) ? $_GET['offset'] : 1;
$offset = ($num - 1) * $limit;

$lock = new Lock ();

$pages = Webpage::query ('id, title, access')
	->order ('title asc')
	->fetch_orig ($limit, $offset);
$count = Webpage::query ()->count ();

foreach ($pages as $k => $p) {
	$pages[$k]->locked = $lock->exists ('Webpage', $p->id);
}

echo $tpl->render ('admin/pages', array (
	'limit' => $limit,
	'total' => $count,
	'pages' => $pages,
	'count' => count ($pages),
	'url' => '/admin/pages?offset=%d'
));

?>