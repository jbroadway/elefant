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

$lang = $this->params[0];

$empty = (isset ($_GET['empty']) && $_GET['empty'] == 1) ? true : false;
if (! $empty) {
	$_GET['empty'] = '';
}

$info = $i18n->languages[$lang];

$page->title = i18n_get ('Editing language') . ': ' . $info['name'];

$all = unserialize (file_get_contents ('lang/_index.php'));
$sources = Translator::get_sources ($all);

require_once ('apps/translator/lib/Functions.php');

if (isset ($_GET['contains']) && ! empty ($_GET['contains'])) {
	$tr = new Translator;
	$all = $tr->translations ($lang, $all);
	$items = Translator::get_by_search ($all, $_GET['contains']);

	echo $tpl->render ('translator/edit_search', array (
		'items' => $items,
		'name' => $info['name'],
		'lang' => $lang,
		'sources' => $sources,
		'contains' => $_GET['contains'],
		'empty' => $empty
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
		'source' => $_GET['source'],
		'empty' => $empty
	));
} else {
	$num = isset ($this->params[1]) ? $this->params[1] : 1;

	$limit = 40;
	$offset = ($num - 1) * $limit;

	if ($empty) {
		$tr = new Translator;
		$all = $tr->translations ($lang, $all);
		foreach ($all as $k => $v) {
			if ($v['trans'] !== '') {
				unset ($all[$k]);
			}
		}
		$items = array_slice ($all, $offset, $limit);
	} else {
		$items = array_slice ($all, $offset, $limit);

		$tr = new Translator;
		$items = $tr->translations ($lang, $items);
	}

	echo $tpl->render ('translator/edit', array (
		'limit' => $limit,
		'total' => count ($all),
		'items' => $items,
		'count' => count ($items),
		'url' => '/translator/edit/' . $lang . '/%d?empty=' . $_GET['empty'],
		'name' => $info['name'],
		'lang' => $lang,
		'sources' => $sources,
		'empty' => $empty
	));
}

?>