<?php

// keep unauthorized users out
$this->require_acl ('admin', $this->app);

// set the layout
$page->layout = 'admin';

// get the version and check if the app installed
$version = Appconf::get ($this->app, 'Admin', 'version');
$current = $this->installed ($this->app, $version);

if ($current === true) {
    // app is already installed and up-to-date, stop here
    $page->title = __ ('Already up-to-date');
    printf ('<p><a href="/%s/admin">%s</a>', $this->app, __ ('Home'));
    return;
}

$page->title = sprintf (
    '%s: %s',
    __ ('Upgrading App'),
    Appconf::get ($this->app, 'Admin', 'name')
);

// grab the database driver
$conn = conf ('Database', 'master');
$driver = $conn['driver'];

// check if upgrade script exists and if so, run it
$file = 'apps/' . $this->app . '/conf/upgrade_' . $version . '_' . $driver . '.sql';
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

// mark the new version installed
$this->mark_installed ($this->app, $version);

printf ('<p><a href="/%s/admin">%s</a></p>', $this->app, __ ('Done.'));

?>