<?php

$this->require_admin ();

if (! file_exists ('lang/_index.php')) {
	$this->redirect ('/translator/build');
}

$page->layout = 'admin';

$page->title = i18n_get ('Languages');

global $i18n;

echo $tpl->render ('translator/index', $i18n);

?>