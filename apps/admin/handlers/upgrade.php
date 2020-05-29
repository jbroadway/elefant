<?php

$page->layout = 'admin';

$this->require_admin ();

if ($this->installed ('elefant', ELEFANT_VERSION) === true) {
	$page->title = __ ('Upgrade completed');
	echo '<p><a href="/blog/admin">' . __ ('Continue') . '</a></p>';
	return;
}

$page->title = __ ('Upgrading Elefant');

DB::single ('select `extra` from `#prefix#webpage` limit 1');
if (DB::error ()) { // Add extra column to webpage
	DB::beginTransaction ();

	if (! DB::execute ('alter table `#prefix#webpage` add column `extra` text')) {
		DB::rollback ();
		printf ('<p>Error: %s</p>', DB::error ());
		return;
	}

	DB::commit ();
}

$db = DB::get_connection (1);
$dbtype = $db->getAttribute (PDO::ATTR_DRIVER_NAME);

if (file_exists ('apps/admin/conf/update/' . ELEFANT_VERSION . '_' . $dbtype . '.sql')) {
	DB::beginTransaction ();
	
	$sqldata = sql_split (file_get_contents ('apps/admin/conf/update/' . ELEFANT_VERSION . '_' . $dbtype . '.sql'));
	
	foreach ($sqldata as $sql) {
		if (! DB::execute ($sql)) {
			DB::rollback ();
			printf ('<p>Error: %s</p>', DB::error ());
			return;
		}
	}
	DB::commit ();
} elseif (ELEFANT_VERSION === '1.3.10') { // Add extra user fields, social links, and notes
	DB::beginTransaction ();
	
	$sqldata = sql_split (file_get_contents ('apps/admin/conf/update/1.3.10_users_' . $dbtype . '.sql'));
	
	foreach ($sqldata as $sql) {
		if (! DB::execute ($sql)) {
			DB::rollback ();
			printf ('<p>Error: %s</p>', DB::error ());
			return;
		}
	}
	DB::commit ();
} elseif (ELEFANT_VERSION === '1.3.6') { // Fix filemanager_prop primary key
	DB::beginTransaction ();

	$db = DB::get_connection (1);
	$dbtype = $db->getAttribute (PDO::ATTR_DRIVER_NAME);
	switch ($dbtype) {
		case 'pgsql':
			if (! DB::execute ('alter table #prefix#filemanager_prop drop constraint #prefix#filemanager_prop_pkey')) {
				DB::rollback ();
				printf ('<p>Error: %s</p>', DB::error ());
				return;
			}

			if (! DB::execute ('alter table #prefix#filemanager_prop add primary key(file, prop)')) {
				DB::rollback ();
				printf ('<p>Error: %s</p>', DB::error ());
				return;
			}

			break;

		case 'mysql':
			if (! DB::execute ('alter table #prefix#filemanager_prop drop primary key')) {
				DB::rollback ();
				printf ('<p>Error: %s</p>', DB::error ());
				return;
			}

			if (! DB::execute ('alter table #prefix#filemanager_prop add primary key(file, prop)')) {
				DB::rollback ();
				printf ('<p>Error: %s</p>', DB::error ());
				return;
			}

			break;

		case 'sqlite':
			if (! DB::execute ('create temporary table #prefix#filemanager_prop_backup (file, prop, value)')) {
				DB::rollback ();
				printf ('<p>Error: %s</p>', DB::error ());
				return;
			}

			if (! DB::execute ('insert into #prefix#filemanager_prop_backup select * from #prefix#filemanager_prop')) {
				DB::rollback ();
				printf ('<p>Error: %s</p>', DB::error ());
				return;
			}

			if (! DB::execute ('drop table #prefix#filemanager_prop')) {
				DB::rollback ();
				printf ('<p>Error: %s</p>', DB::error ());
				return;
			}

			if (! DB::execute ('create table #prefix#filemanager_prop (
				file char(128) not null,
				prop char(32) not null,
				value char(255) not null,
				primary key (file, prop)
			)')) {
				DB::rollback ();
				printf ('<p>Error: %s</p>', DB::error ());
				return;
			}

			if (! DB::execute ('insert into #prefix#filemanager_prop select * from #prefix#filemanager_prop_backup')) {
				DB::rollback ();
				printf ('<p>Error: %s</p>', DB::error ());
				return;
			}

			if (! DB::execute ('drop table #prefix#filemanager_prop_backup')) {
				DB::rollback ();
				printf ('<p>Error: %s</p>', DB::error ());
				return;
			}

			break;
	}

	DB::commit ();
}

printf ('<p><a href="/">%s</a></p>', __ ('Done.'));

$this->mark_installed ('elefant', ELEFANT_VERSION);
