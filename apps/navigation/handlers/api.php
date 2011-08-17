<?php

$page->template = false;
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
	default:
		$error = 'Unknown method';
		break;
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