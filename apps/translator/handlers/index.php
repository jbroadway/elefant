<?php

/**
 * List of languages.
 */

$this->require_acl ('admin', 'translator');

if (! file_exists ('lang/_index.php')) {
	$this->redirect ('/translator/build');
}

require_once ('apps/translator/lib/Functions.php');

$page->layout = 'admin';

$page->title = __ ('Languages');

echo $tpl->render ('translator/index', $i18n);

?>