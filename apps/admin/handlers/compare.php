<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$ver = new Versions ($_GET['id']);
$old = $ver->restore ();
$class = $ver->class;
$cur = new $class ($ver->pkey);
$diff = Versions::diff ($old, $cur);
$data = array ();
foreach ((array) $cur->orig () as $key => $value) {
	$data[$key] = array (
		'cur' => $value,
		'old' => $old->{$key},
		'diff' => (in_array ($key, $diff)) ? true : false
	);
}

$page->title = i18n_get ('Comparing') . ' ' . $ver->class . ' / ' . $ver->pkey;

echo $tpl->render ('admin/compare', array (
	'fields' => $data,
	'class' => $ver->class,
	'pkey' => $ver->pkey,
	'ts' => $ver->ts
));

?>