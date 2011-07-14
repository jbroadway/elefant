<?php

$page->layout = 'admin';

if (! User::require_admin ()) {
	header ('Location: /admin');
	exit;
}

$ver = new Versions ($_GET['id']);

$lock = new Lock ($ver->class, $ver->pkey);
if ($lock->exists ()) {
	$page->title = i18n_get ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
} else {
	$lock->add ();
}

$obj = $ver->restore ();
$obj->put ();
if ($obj->error) {
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $obj->error;
	return;
}
Versions::add ($obj);

$page->title = i18n_get ('Item Restored');
echo '<p>The item has been restored.</p>';
if ($ver->class == 'Webpage') {
	echo '<p><a href="/' . $obj->id . '">Continue</a></p>';
} else {
	echo '<p><a href="/">Continue</a></p>';
}

?>