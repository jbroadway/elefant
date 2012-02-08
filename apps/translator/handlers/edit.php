<?php

$this->require_admin ();

if (! file_exists ('lang/_index.php')) {
	$this->redirect ('/translator/build');
}

$page->layout = 'admin';

global $i18n;

list ($lang, $num) = $this->params;

$info = $i18n->languages[$lang];

$page->title = i18n_get ('Editing language') . ': ' . $info['name'];

$limit = 40;
$offset = ($num - 1) * $limit;

$all = unserialize (file_get_contents ('lang/_index.php'));
$items = array_slice ($all, $offset, $limit);

$tr = new Translator;
$items = $tr->getTranslations ($lang, $items);

require_once ('apps/translator/lib/Functions.php');

echo $tpl->render ('translator/edit', array (
	'limit' => $limit,
	'total' => count ($all),
	'items' => $items,
	'count' => count ($items),
	'url' => '/translator/edit/' . $lang . '/%d',
	'name' => $info['name']
));

?>