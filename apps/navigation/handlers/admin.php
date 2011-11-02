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
$page->add_script ('<script src="/apps/navigation/js/jquery.jstree.js"></script>');

// get ids already in tree to skip
$nav = new Navigation;
$ids = $nav->get_all_ids ();

// build other page list
require_once ('apps/navigation/lib/Functions.php');
$pages = navigation_get_other_pages ($ids);

echo $tpl->render ('navigation/admin', array (
	'pages' => $pages
));

?>