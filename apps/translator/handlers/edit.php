<?php

/**
 * Edit the translation strings for a language. Shows 40 per page
 * and includes highlighting missing strings, references with
 * source file on hover, and auto-save.
 */

$this->require_admin ();

if (! file_exists ('lang/_index.php')) {
	$this->redirect ('/translator/build');
}

$page->layout = 'admin';

global $i18n;

$lang = $this->params[0];

$info = $i18n->languages[$lang];

$page->title = i18n_get ('Editing language') . ': ' . $info['name'];

$all = unserialize (file_get_contents ('lang/_index.php'));
$sources = Translator::get_sources ($all);

require_once ('apps/translator/lib/Functions.php');

if (isset ($_GET['contains']) && ! empty ($_GET['contains'])) {
	$items = Translator::get_by_search ($all, $_GET['contains']);

	$tr = new Translator;
	$items = $tr->translations ($lang, $items);

	echo $tpl->render ('translator/edit_search', array (
		'items' => $items,
		'name' => $info['name'],
		'lang' => $lang,
		'sources' => $sources,
		'contains' => $_GET['contains']
	));
} elseif (isset ($_GET['source']) && ! empty ($_GET['source'])) {
	$items = Translator::get_by_source ($all, $_GET['source']);

	$tr = new Translator;
	$items = $tr->translations ($lang, $items);

	echo $tpl->render ('translator/edit_source', array (
		'items' => $items,
		'name' => $info['name'],
		'lang' => $lang,
		'sources' => $sources,
		'source' => $_GET['source']
	));
} else {
	$num = isset ($this->params[1]) ? $this->params[1] : 1;

	$limit = 40;
	$offset = ($num - 1) * $limit;

	$items = array_slice ($all, $offset, $limit);

	$tr = new Translator;
	$items = $tr->translations ($lang, $items);

	echo $tpl->render ('translator/edit', array (
		'limit' => $limit,
		'total' => count ($all),
		'items' => $items,
		'count' => count ($items),
		'url' => '/translator/edit/' . $lang . '/%d',
		'name' => $info['name'],
		'lang' => $lang,
		'sources' => $sources
	));
}

?>