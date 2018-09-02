<?php

/**
 * Provides the navigation editing capabilities for admins to add pages
 * and reorganize them in the site tree.
 */

$this->require_acl ('admin', 'navigation');

$page->title = __ ('Navigation');
$page->layout = 'admin';

$this->run ('admin/util/twemoji');

$page->add_script ('<script src="/js/jquery-ui/jquery-ui.min.js"></script>', 'tail');
$page->add_script ('<script src="/apps/navigation/js/tree-drag-drop/tree-drag-drop.js"></script>', 'tail');
$page->add_style ('/apps/admin/css/font-awesome/css/font-awesome.min.css');

// get ids already in tree to skip
$nav = new Navigation;
$ids = $nav->get_all_ids ();

// build other page list
require_once ('apps/navigation/lib/Functions.php');
$other_pages = navigation_get_other_pages ($ids);

echo $tpl->render ('navigation/admin', array (
	'other_pages' => $other_pages
));
