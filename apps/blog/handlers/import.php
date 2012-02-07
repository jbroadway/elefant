<?php

/**
 * Lists available importers.
 */

$this->require_admin ();

$page->layout = 'admin';

$page->title = i18n_get ('Choose an importer');

echo $tpl->render ('blog/import');

?>