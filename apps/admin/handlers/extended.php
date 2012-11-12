<?php

/**
 * Edit custom fields for a given type.
 */

$this->require_admin ();

if (! isset ($_GET['extends'])) {
	echo $this->error (500, __ ('Unknown error'));
	return;
}

if (! class_exists ($_GET['extends'])) {
	echo $this->error (500, __ ('Unknown error'));
	return;
}

if (! isset ($_GET['name'])) {
	$_GET['name'] = $_GET['extends'];
}

$page->layout = 'admin';
$page->title = __ ('Custom Fields') . ': ' . __ ($_GET['name']);
$page->add_script ('/apps/admin/js/handlebars-1.0.rc.1.js');
$page->add_script ('/apps/admin/js/jquery-ui.min.js');
$page->add_script ('/apps/admin/js/extended.js');

$data = array ('extends' => $_GET['extends']);
$data['fields'] = ExtendedFields::for_class ($_GET['extends']);
if (! is_array ($data['fields'])) {
	$data['fields'] = array ();
}

echo $tpl->render ('admin/extended', $data);

?>