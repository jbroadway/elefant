<?php

/**
 * Compares a previous version of a Model object to the
 * current version, allowing you to restore the previous
 * version if desired.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$ver = new Versions ($_GET['id']);
$old = $ver->restore ();
$class = $ver->class;
$cur = new $class ($ver->pkey);
if ($cur->error) {
	// deleted item
	foreach (json_decode ($ver->serialized) as $key => $value) {
		$cur->{$key} = $value;
	}
}
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