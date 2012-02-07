<?php

/**
 * Show version history of an object, with the ability
 * to compare to the current version.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$limit = 20;
$_GET['offset'] = (isset ($_GET['offset'])) ? $_GET['offset'] : 0;
$_GET['type'] = (isset ($_GET['type'])) ? $_GET['type'] : 'Webpage';

$classes = Versions::get_classes ();
$deleted = false;

if (isset ($_GET['type'])) {
	$class = $_GET['type'];
	if (isset ($_GET['id']) && ! empty ($_GET['id'])) {
		$obj = new $class ($_GET['id']);
		if ($obj->error) {
			// deleted item
			$obj->{$obj->key} = $_GET['id'];
			$deleted = true;
		}
	} else {
		$obj = $class;
	}
	$history = Versions::history ($obj, $limit, $_GET['offset']);
	$count = Versions::history ($obj, true);
} else {
	$history = array ();
	$count = 0;
}

function admin_filter_user_name ($id) {
	$u = new User ($id);
	if ($u->error) {
		return i18n_get ('Nobody');
	}
	return $u->name;
}

$page->title = i18n_get ('Versions of') . ' ' . $_GET['type'];
if (! empty ($_GET['id'])) {
	$page->title .= ' / ' . $_GET['id'];
}

echo $tpl->render ('admin/versions', array (
	'id' => (! empty ($_GET['id'])) ? $_GET['id'] : false,
	'type' => $_GET['type'],
	'classes' => $classes,
	'history' => $history,
	'count' => $count,
	'offset' => $_GET['offset'],
	'more' => ($count > $_GET['offset'] + $limit) ? true : false,
	'prev' => $_GET['offset'] - $limit,
	'next' => $_GET['offset'] + $limit,
	'deleted' => $deleted
));

?>