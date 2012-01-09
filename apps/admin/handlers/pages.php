<?php

$this->require_admin ();

$page->layout = 'admin';
$page->title = i18n_get ('All Pages');

$limit = 20;
$_GET['offset'] = (isset ($_GET['offset'])) ? $_GET['offset'] : 0;

$lock = new Lock ();

$pages = Webpage::query ('id, title, access')
	->order ('title asc')
	->fetch_orig ($limit, $_GET['offset']);
$count = Webpage::query ()->count ();

foreach ($pages as $k => $p) {
	$pages[$k]->locked = $lock->exists ('Webpage', $p->id);
}

echo $tpl->render ('admin/pages', array (
	'pages' => $pages,
	'count' => $count,
	'offset' => $_GET['offset'],
	'more' => ($count > $_GET['offset'] + $limit) ? true : false,
	'prev' => $_GET['offset'] - $limit,
	'next' => $_GET['offset'] + $limit
));

?>