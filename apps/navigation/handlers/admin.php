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
$pages = array ();
$res = db_fetch_array ('select id, title, menu_title from webpage where access = "public"');
foreach ($res as $p) {
	if (in_array ($p->id, $ids)) {
		// skip if in tree
		continue;
	}
	if (! empty ($p->menu_title)) {
		$pages[$p->id] = $p->menu_title;
	} else {
		$pages[$p->id] = $p->title;
	}
}

echo $tpl->render ('navigation/admin', array (
	'pages' => $pages
));

?>