<?php

/**
 * Lists all content blocks for editing.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'blocks');

$limit = 20;
$num = isset ($_GET['offset']) ? $_GET['offset'] : 1;
$offset = ($num - 1) * $limit;

$lock = new Lock ();

$blocks = Block::query ('id, title, access')
	->order ('id asc')
	->fetch_orig ($limit, $offset);
$count = Block::query ()->count ();

foreach ($blocks as $k => $b) {
	$blocks[$k]->locked = $lock->exists ('Block', $b->id);
}

$page->title = __ ('Blocks');
echo $tpl->render ('blocks/admin', array (
	'limit' => $limit,
	'total' => $count,
	'blocks' => $blocks,
	'count' => count ($blocks),
	'url' => '/blocks/admin?offset=%d'
));

?>