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

// Create the database table if it doesn't exist
if (! DB::single ('select count(*) from #prefix#extended_fields')) {
	$db = DB::get_connection (true);
	$queries = sql_split (
		file_get_contents (
			sprintf (
				'apps/admin/conf/update/extended_fields_%s.sql',
				$db->getAttribute (PDO::ATTR_DRIVER_NAME)
			)
		)
	);
	foreach ($queries as $query) {
		DB::execute ($query);
	}
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

$res = glob ('apps/*/conf/fields.php');
$res = is_array ($res) ? $res : array ();
$data['custom'] = array ();
foreach ($res as $file) {
	$fields = parse_ini_file ($file, true);
	foreach ($fields as $field => $settings) {
		$data['custom'][preg_replace ('/[^a-zA-Z_]+/', '_', $field)] = $settings['name'];
	}
}

echo $tpl->render ('admin/extended', $data);

?>