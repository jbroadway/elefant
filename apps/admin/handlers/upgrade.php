<?php

$page->layout = 'admin';

$this->require_admin ();

if ($this->installed ('elefant', ELEFANT_VERSION) === true) {
	$page->title = __ ('Upgrade completed');
	echo '<p><a href="/blog/admin">' . __ ('Continue') . '</a></p>';
	return;
}

$page->title = __ ('Upgrading Elefant');

$db = DB::get_connection (1);
$dbtype = $db->getAttribute (PDO::ATTR_DRIVER_NAME);

$version = ELEFANT_VERSION;
$current = $this->installed ('elefant', $version);

// get the base new version and current version for comparison
$base_version = preg_replace ('/-.*$/', '', $version);
$base_current = preg_replace ('/-.*$/', '', $current);

// find upgrade scripts to apply
$files = glob ('apps/admin/conf/update/*_' . $dbtype . '.sql');
$apply = array ();
foreach ($files as $k => $file) {
	if (preg_match ('/^apps\/admin\/conf\/update\/([0-9\.-]+)_' . $dbtype . '\.sql$/', $file, $regs)) {
		if (version_compare ($regs[1], $base_current, '>') && version_compare ($regs[1], $base_version, '<=')) {
			$apply[$regs[1]] = $file;
		}
	}
}

if (count ($apply) > 0) {
	// begin the transaction
	DB::beginTransaction ();

	// apply the upgrade scripts
	foreach ($apply as $ver => $file) {
		// parse the database schema into individual queries
		$sql = sql_split (file_get_contents ($file));

		// execute each query in turn
		foreach ($sql as $query) {
			if (! DB::execute ($query)) {
				// show error and rollback on failures
				printf (
					'<p>%s</p><p class="visible-notice">%s: %s</p>',
					__ ('Upgrade failed on version %s. Rolling back changes.', $ver),
					__ ('Error'),
					DB::error ()
				);
				DB::rollback ();
				return;
			}
		}

		// add any custom upgrade logic here
	}

	// commit the transaction
	DB::commit ();
}

// Older updates past this point (can be removed in a future release)

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

if (ELEFANT_VERSION === '1.3.10') { // Add extra user fields, social links, and notes
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
}

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
