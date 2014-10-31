<?php

$page->layout = 'admin';

$this->require_admin ();

if ($this->installed ('user', $appconf['Admin']['version']) === true) {
	$page->title = __ ('Upgrade completed');
	echo '<p><a href="/user/admin">' . __ ('Continue') . '</a></p>';
	return;
}

$page->title = __ ('Upgrading User App');

$db = DB::get_connection (1);
$dbtype = $db->getAttribute (PDO::ATTR_DRIVER_NAME);
switch ($dbtype) {
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
