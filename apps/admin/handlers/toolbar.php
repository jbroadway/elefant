<?php

$this->require_acl('admin','admin/toolbar');

$page->title = __ ('Toolbar Editor');
$page->layout = 'admin';

$this->run ('admin/util/modal');
$page->add_script ('<script src="/apps/admin/js/tree-drag-drop/jquery-ui-1.9.1.custom.min.js"></script>', 'tail');
$page->add_script ('<script src="/apps/admin/js/tree-drag-drop/tree-drag-drop.js"></script>', 'tail');
$page->add_style ('/apps/admin/css/font-awesome/css/font-awesome.min.css');
$page->add_style ('/apps/admin/js/tree-drag-drop/css/tree-drag-drop.css');

list($tools, $apps) = admin\Toolbar::compile ($this);
echo $tpl->render ('admin/toolbar', array (
	'tools' => $tools,
	'autofill' => admin\Toolbar::$autofill,
	'apps' => $apps
));