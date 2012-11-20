<?php

/**
 * Provides the underlying API for the drag and drop capabilities
 * in the navigation editor.
 */

$page->layout = false;
header ('Content-Type: application/json');

if (! User::require_admin ()) {
	$res = new StdClass;
	$res->success = false;
	$res->error = 'Authorization required.';
	header ('WWW-Authenticate: Basic realm="Navigation"');
	header ('HTTP/1.0 401 Unauthorized');
	echo json_encode ($res);
	return;
}

$error = false;
$out = null;
$nav = new Navigation;

switch ($this->params[0]) {	
	case 'update':
		$tree = $_POST['tree'];	
		if(empty($tree)){
			$tree =  array();
		}
		require_once ('apps/navigation/lib/Functions.php');		
		if ($nav->update ($tree) && $nav->save ()) {
			$out = array (
				'msg' => sprintf ('Nav json has been updated'),
			);
		} else {
			$error = $nav->error;
		}
		break;	
	default:
		$error = 'Unknown method';
		break;
}

if (! $error) {
	require_once ('apps/navigation/lib/Functions.php');
	navigation_clear_cache ();
}

$res = new StdClass;
if ($error) {
	$res->success = false;
	$res->error = $error;
} else {
	$res->success = true;
	$res->data = $out;
}

echo json_encode ($res);

?>