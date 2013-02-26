<?php

/**
 * Saves edits in the background for the `Save & Keep Editing` button
 * in the edit form.
 */

$page->layout = false;
header ('Content-Type: application/json');

if (! User::require_admin ()) {
	$res = new StdClass;
	$res->success = false;
	$res->error = __ ('Authorization required.');
	echo json_encode ($res);
	return;
}

$error = false;

$o = new Block ($_GET['id']);
if ($o->error) {
	$error = $o->error;
} else {
	foreach ($_POST as $k => $v) {
		if ($k != $o->key && isset ($o->data[$k])) {
			$o->{$k} = $v;
		}
	}

	if (! $o->put ()) {
		$error = $o->error;
	} else {
		Versions::add ($o);
		$_POST['id'] = $_GET['id'];
		$this->hook ('blocks/edit', $_POST);
	}
}

$res = new StdClass;
if ($error) {
	$res->success = false;
	$res->error = $error;
} else {
	$res->success = true;
	$res->data = $_GET['id'];
}

echo json_encode ($res);

?>