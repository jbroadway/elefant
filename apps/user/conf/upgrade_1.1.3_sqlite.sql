begin transaction;
alter table `#prefix#user` rename to `tmp_user`;
create table #prefix#user (
	id integer primary key,
	email char(72) unique not null,
	password char(128) not null,
	session_id char(32) unique,
	expires datetime not null,
	name char(72) not null,
	type char(32) not null,
	signed_up datetime not null,
	updated datetime not null,
	userdata text not null,
	phone char(32) not null default '',
	address char(48) not null default '',
	address2 char(48) not null default '',
	city char(48) not null default '',
	state char(3) not null default '',
	country char(3) not null default '',
	zip char(16) not null default '',
	title char(48) not null default '',
	company char(48) not null default '',
	photo char(128) not null default '',
	about text not null default '',
	website char(128) not null default ''
);
create index #prefix#user_email_password on #prefix#user (email, password);
create index #prefix#user_session_id on #prefix#user (session_id);
insert into `#prefix#user` (id, email, password, session_id, expires, name, type, signed_up, updated, userdata)
	select id, email, password, session_id, expires, name, type, signed_up, updated, userdata from `tmp_user`;
drop table `tmp_user`;
commit;
