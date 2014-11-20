<?php

/**
 * Lists available importers.
 */

$this->require_acl ('admin', 'user');

$page->layout = 'admin';

$page->title = __ ('Choose an importer');

echo $tpl->render ('user/import');
