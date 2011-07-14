<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$limit = 20;
$_GET['offset'] = (isset ($_GET['offset'])) ? $_GET['offset'] : 0;

$lock = new Lock ();

$blocks = Block::query ('id, title, access')
	->order ('id asc')
	->fetch_orig ($limit, $_GET['offset']);
$count = Block::query ()->count ();

foreach ($blocks as $k => $b) {
	$blocks[$k]->locked = $lock->exists ('Block', $b->id);
}

$page->title = i18n_get ('Blocks');
echo $tpl->render ('blocks/admin', array (
	'blocks' => $blocks,
	'count' => $count,
	'offset' => $_GET['offset'],
	'more' => ($count > $_GET['offset'] + $limit) ? true : false,
	'prev' => $_GET['offset'] - $limit,
	'next' => $_GET['offset'] + $limit
));

?>