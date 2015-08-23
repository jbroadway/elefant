<?php

$page->layout = 'admin';

$this->require_admin ();

if ($this->installed ('user', $appconf['Admin']['version']) === true) {
	$page->title = __ ('Upgrade completed');
	echo '<p><a href="/user/admin">' . __ ('Continue') . '</a></p>';
	return;
}

$page->title = __ ('Upgrading User App');

// grab the database driver
$conn = conf ('Database', 'master');
$driver = $conn['driver'];

// check if upgrade script exists and if so, run it
$base_version = preg_replace ('/-.*$/', '', $version);
$file = 'apps/' . $this->app . '/conf/upgrade_' . $base_version . '_' . $driver . '.sql';
if (file_exists ($file)) {
    // begin the transaction
    DB::beginTransaction ();

    // parse the database schema into individual queries
    $sql = sql_split (file_get_contents ($file));

    // execute each query in turn
    foreach ($sql as $query) {
        if (! DB::execute ($query)) {
            // show error and rollback on failures
            printf (
                '<p class="visible-notice">%s: %s</p><p>%s</p>',
                __ ('Error'),
                DB::error (),
                __ ('Install failed.')
            );
            DB::rollback ();
            return;
        }
    }

    // commit the transaction
    DB::commit ();
}

// add your upgrade logic here

echo '<p>' . __ ('Done.') . '</p>';

$this->mark_installed ('user', $appconf['Admin']['version']);
