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
$cur_orig = (array) $cur->orig ();
$old_orig = (array) $old->orig ();
foreach ($cur_orig as $key => $value) {
	$data[$key] = array (
		'cur' => $value,
		'old' => $old_orig[$key],
		'diff' => (in_array ($key, $diff)) ? true : false
	);
}

if (is_subclass_of ($cur, 'ExtendedModel')) {
	unset ($data[$cur->_extended_field]);
}

// render grid if enabled
if ($ver->class === 'Webpage' && conf ('General', 'page_editor') === 'grid') {
	$data['body'] = $data['grid'];
	$data['body']['cur'] = $this->run (
		'admin/grid',
		array (
			'id' => $_GET['id'],
			'grid' => new admin\Grid ($data['body']['cur']),
			'preview' => true
		)
	);
	$data['body']['old'] = $this->run (
		'admin/grid',
		array (
			'id' => $_GET['id'],
			'grid' => new admin\Grid ($data['body']['old']),
			'preview' => true
		)
	);
	$data['body']['diff'] = ($data['body']['cur'] !== $data['body']['old']);
	unset ($data['grid']);
}

$page->title = __ ('Comparing') . ' ' . $ver->class . ' / ' . $ver->pkey;

echo $tpl->render ('admin/compare', array (
	'fields' => $data,
	'class' => $ver->class,
	'pkey' => $ver->pkey,
	'ts' => $ver->ts
));
