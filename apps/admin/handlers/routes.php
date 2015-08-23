<?php

$this->require_acl('admin','admin/routes');

$page->title = __ ('Routes Editor');
$page->layout = 'admin';

$this->run ('admin/util/modal');
$this->run ('admin/util/notifier');
$page->add_style ('/apps/admin/css/font-awesome/css/font-awesome.min.css');
$page->add_style ('/apps/admin/js/routes.js');
$page->add_style ('/apps/admin/css/routes.css');
$path = conf_env_path('routes');
if (!file_exists($path)) $path = 'conf/routes.php';
$routes = Ini::parse($path,true);
echo $tpl->render ('admin/routes', $routes);