<?php

$page->layout = 'admin';

$this->require_admin ();

if ($this->installed ('elefant', ELEFANT_VERSION) === true) {
	$page->title = __ ('Upgrade completed');
	echo '<p><a href="/blog/admin">' . __ ('Continue') . '</a></p>';
	return;
}

$page->title = __ ('Upgrading Elefant');

if (ELEFANT_VERSION === '1.3.6') { // Fix filemanager_prop primary key
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

?>