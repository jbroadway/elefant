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
    printf ('<p><a href="/%s">%s</a>', Appconf::get ($this->app, 'Admin', 'handler'), __ ('Home'));
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

// get the base new version and current version for comparison
$base_version = preg_replace ('/-.*$/', '', $version);
$base_current = preg_replace ('/-.*$/', '', $current);

// find upgrade scripts to apply
$files = glob ('apps/' . $this->app . '/conf/upgrade_*_' . $driver . '.sql');
$apply = array ();
foreach ($files as $k => $file) {
    if (preg_match ('/^apps\/' . $this->app . '\/conf\/upgrade_([0-9.]+)_' . $driver . '\.sql$/', $file, $regs)) {
        if (version_compare ($regs[1], $base_current, '>') && version_compare ($regs[1], $base_version, '<=')) {
            $apply[$regs[1]] = $file;
        }
    }
}

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

// mark the new version installed
$this->mark_installed ($this->app, $version);

printf ('<p><a href="/%s">%s</a>', Appconf::get ($this->app, 'Admin', 'handler'), __ ('Done.'));
