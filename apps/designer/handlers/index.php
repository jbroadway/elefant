<?php

/**
 * Displays a list of layout templates and stylesheets in two tabs.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$lock = new Lock ();

$out = array (
	'layouts' => glob ('layouts/*.html'),
	'layouts2' => glob ('layouts/*/*.html'),
	'stylesheets' => glob ('css/*.css'),
	'stylesheets2' => glob ('layouts/*/*.css'),
	'locks' => array ()
);

foreach ($out['layouts2'] as $name) {
	$out['layouts'][] = $name;
}

foreach ($out['stylesheets2'] as $name) {
	$out['stylesheets'][] = $name;
}

foreach ($out['layouts'] as $name) {
	$out['locks'][$name] = $lock->exists ('Designer', $name);
}

foreach ($out['stylesheets'] as $name) {
	$out['locks'][$name] = $lock->exists ('Designer', $name);
}

function basename_html ($f) {
	return basename ($f, '.html');
}

$page->title = i18n_get ('Designer');
echo $tpl->render ('designer/index', $out);

?>