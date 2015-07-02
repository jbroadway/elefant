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

// increase password field length
switch ($driver) {
	case 'pgsql':
		DB::execute ('alter table "#prefix#user" alter column "password" type varchar(128)');
		break;
	case 'mysql':
		DB::execute ('alter table `#prefix#user` change column `password` `password` varchar(128) not null');
		break;
	case 'sqlite':
		DB::execute ('begin transaction');
		DB::execute ('alter table `#prefix#user` rename to `tmp_user`');
		DB::execute ('create table #prefix#user (
			id integer primary key,
			email char(72) unique not null,
			password char(128) not null,
			session_id char(32) unique,
			expires datetime not null,
			name char(72) not null,
			type char(32) not null,
			signed_up datetime not null,
			updated datetime not null,
			userdata text not null
		)');
		DB::execute ('create index #prefix#user_email_password on #prefix#user (email, password)');
		DB::execute ('create index #prefix#user_session_id on #prefix#user (session_id)');
		DB::execute ('insert into `#prefix#user` (id, email, password, session_id, expires, name, type, signed_up, updated, userdata)
			select id, email, password, session_id, expires, name, type, signed_up, updated, userdata from `tmp_user`');
		DB::execute ('drop table `tmp_user`');
		DB::execute ('commit');
		break;
}

echo '<p>' . __ ('Done.') . '</p>';

$this->mark_installed ('user', $appconf['Admin']['version']);
