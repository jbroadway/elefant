<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$out = array (
	'layouts' => glob ('layouts/*.html'),
	'stylesheets' => glob ('css/*.css')
);

$page->title = i18n_get ('Designer');
echo $tpl->render ('designer/index', $out);

?>