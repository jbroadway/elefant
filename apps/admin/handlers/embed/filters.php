<?php

/**
 * Apply filters for dynamic embed data.
 * Called by apps/admin/js/jquery.dynamicobjects.js
 */

$page->layout = false;
header ('Content-Type: application/json');

if (! User::require_admin ()) {
	echo json_encode ((object) array ('success' => false, 'error' => 'Must be logged in', 'data' => $_POST['data']));
	return;
}

list ($app, $extra) = explode ('/', $_POST['handler']);

if (! file_exists ('apps/' . $app . '/conf/embed.php')) {
	echo json_encode ((object) array ('success' => false, 'error' => 'Embed file not found', 'data' => $_POST['data']));
	return;
}

$rules = parse_ini_file ('apps/' . $app . '/conf/embed.php', true);

if (! isset ($rules[$_POST['handler']])) {
	echo json_encode ((object) array ('success' => false, 'error' => 'No embed data', 'data' => $_POST['data']));
	return;
}

$reverse = (isset ($_POST['reverse']) && $_POST['reverse'] === 'yes') ? true : false;

// apply filters
$out = array ();
foreach ($_POST['data'] as $key => $value) {
	if (! isset ($rules[$_POST['handler']][$key]['filter'])) {
		// no filter
		$out[$key] = $value;
	} else {
		if (isset ($rules[$_POST['handler']][$key]['require'])) {
			require_once ($rules[$_POST['handler']][$key]['require']);
		}
		$out[$key] = $rules[$_POST['handler']][$key]['filter'] ($value, $reverse);
	}
}

echo json_encode ((object) array ('success' => true, 'data' => $out));

?>