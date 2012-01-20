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
	case 'add':
		$id = $_POST['page'];
		$parent = $_POST['parent'];
		if ($parent === 'false') {
			$parent = false;
		}
		if ($nav->add ($id, $parent) && $nav->save ()) {
			$out = array (
				'msg' => sprintf ('Page %s added to tree under %s.', $id, $parent),
				'page' => $id,
				'parent' => $parent
			);
		} else {
			$error = $nav->error;
		}
		break;
	case 'move':
		$id = $_POST['page'];
		$ref = $_POST['ref'];
		$pos = $_POST['pos'];
		if ($nav->move ($id, $ref, $pos) && $nav->save ()) {
			$out = array (
				'msg' => sprintf ('Page %s moved to %s %s.', $id, $pos, $ref),
				'page' => $id,
				'ref' => $ref,
				'pos' => $pos
			);
		} else {
			$error = $nav->error;
		}
		break;
	case 'remove':
		$id = $_POST['page'];
		if ($nav->remove ($id) && $nav->save ()) {
			require_once ('apps/navigation/lib/Functions.php');
			$ids = $nav->get_all_ids ();
			$out = array (
				'msg' => sprintf ('Page %s removed.', $id),
				'page' => $id,
				'other' => navigation_get_other_pages ($ids)
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