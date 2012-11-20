<?php

/**
 * Provides the navigation editing capabilities for admins to add pages
 * and reorganize them in the site tree.
 */

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$page->title = i18n_get ('Navigation');
$page->layout = 'admin';

$page->add_script ('<script src="/apps/navigation/js/tree-drag-drop/jquery-ui-1.9.1.custom.min.js"></script>', 'tail');
$page->add_script ('<script src="/apps/navigation/js/tree-drag-drop/tree-drag-drop.js"></script>', 'tail');

if (detect ('msie 7')) {
	$page->add_style ('/apps/admin/css/font-awesome/css/font-awesome-ie7.css');
}
$page->add_style ('/apps/admin/css/font-awesome/css/font-awesome.css');

// get ids already in tree to skip
$nav = new Navigation;
$ids = $nav->get_all_ids ();

// build other page list
require_once ('apps/navigation/lib/Functions.php');
$other_pages = navigation_get_other_pages ($ids);

echo $tpl->render ('navigation/admin', array (
	'other_pages' => $other_pages
));

?>