<?php

$page->layout = 'admin';

$this->require_admin ();

if ($this->installed ('filemanager', $appconf['Admin']['version']) === true) {
	$page->title = __ ('Upgrade completed');
	echo '<p><a href="/filemanager/index">' . __ ('Continue') . '</a></p>';
	return;
}

$page->title = __ ('Upgrading Files App');

$db = DB::get_connection (1);
$dbtype = $db->getAttribute (PDO::ATTR_DRIVER_NAME);
switch ($dbtype) {
	case 'mysql':
		DB::execute ('create table #prefix#filemanager_prop (
			file char(128) not null primary key,
			prop char(32) not null,
			value char(255) not null,
			index (prop);
		)');
		break;
	case 'pgsql':
	case 'sqlite':
		DB::execute ('create table #prefix#filemanager_prop (
			file char(128) not null primary key,
			prop char(32) not null,
			value char(255) not null
		)');
		DB::execute ('create index #prefix#filemanager_prop_name on #prefix#filemanager_prop (prop)');
		break;
}
echo '<p>' . __ ('Done.') . '</p>';

$this->mark_installed ('filemanager', $appconf['Admin']['version']);
