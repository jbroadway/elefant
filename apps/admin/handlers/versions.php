<?php

/**
 * Show version history of an object, with the ability
 * to compare to the current version.
 */

$page->layout = 'admin';

$this->require_admin ();

$limit = 20;
$num = isset ($_GET['offset']) ? $_GET['offset'] : 1;
$offset = ($num - 1) * $limit;
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
	$history = Versions::history ($obj, $limit, $offset);
	$count = Versions::history ($obj, true);
} else {
	$history = array ();
	$count = 0;
}

function admin_filter_user_name ($id) {
	$u = new User ($id);
	if ($u->error) {
		return __ ('Nobody');
	}
	return $u->name;
}

$page->title = __ ('Versions of') . ' ' . Template::sanitize ($_GET['type']);
if (! empty ($_GET['id'])) {
	$page->title .= ' / ' . Template::sanitize ($_GET['id']);
}

echo $tpl->render ('admin/versions', array (
	'id' => (! empty ($_GET['id'])) ? $_GET['id'] : false,
	'type' => $_GET['type'],
	'classes' => $classes,
	'history' => $history,
	'limit' => $limit,
	'total' => $count,
	'count' => count ($history),
	'url' => sprintf ('/admin/versions?type=%s&id=%s&offset=%%d', $_GET['type'], $_GET['id']),
	'deleted' => $deleted
));

?>