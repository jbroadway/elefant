<?php

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

$page->title = i18n_get ('Designer');
echo $tpl->render ('designer/index', $out);

?>