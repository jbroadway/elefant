<?php

$this->require_admin ();

if (! file_exists ('lang/_index.php')) {
	$this->redirect ('/translator/build');
}

$page->layout = 'admin';

$page->title = i18n_get ('Languages');

list ($lang, $num) = $this->params;
$limit = 40;
$offset = $num * $limit;

$all = unserialize (file_get_contents ('lang/_index.php'));
$items = array_slice ($all, $offset, $limit);

echo $tpl->render ('translator/edit', array (
	'limit' => $limit,
	'total' => count ($all),
	'items' => $items,
	'count' => count ($items),
	'url' => '/translator/edit/' . $lang . '/%d'
));

?>