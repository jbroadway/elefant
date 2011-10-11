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
	'stylesheets' => glob ('css/*.css'),
	'locks' => array ()
);

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