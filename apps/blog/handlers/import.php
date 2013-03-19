<?php

/**
 * Lists available importers.
 */

$this->require_admin ();

$page->layout = 'admin';

$page->title = __ ('Choose an importer');

echo $tpl->render ('blog/import');

?>