begin;

create table #prefix#webpage (
	id varchar(72) not null primary key,
	title varchar(72) not null,
	menu_title varchar(72) not null,
	window_title varchar(72) not null,
	access varchar(10) not null default 'public',
	layout varchar(48) not null,
	description text,
	keywords text,
	body text,
	extra text,
	check (access in ('public','member','private'))
);

create index #prefix#webpage_access on #prefix#webpage (access);

insert into #prefix#webpage (id, title, menu_title, window_title, access, layout, description, keywords, body, extra) values ('index', 'Welcome to Elefant', 'Home', '', 'public', 'default', '', '', '<table><tbody><tr><td><h3>Congratulations!</h3>You have successfully installed Elefant, the refreshingly simple new PHP web framework and CMS.</td><td><h3>Getting Started</h3>To log in as an administrator and edit pages, write a blog post, or upload files, go to <a href="/admin">/admin</a>.</td><td><h3>Developers</h3>Documentation, source code and issue tracking can be found at <a href="http://github.com/jbroadway/elefant">github.com/jbroadway/elefant</a></td></tr></tbody></table>', '[]');


create table #prefix#block (
	id varchar(72) not null primary key,
	title varchar(72) not null,
	body text,
	access varchar(10) not null default 'public',
	show_title varchar(3) not null default 'yes',
	check (access in ('public','member','private')),
	check (show_title in ('yes','no'))
);

create index #prefix#block_access on #prefix#block (id, access);

insert into #prefix#block (id, title, access, body, show_title) values ('members', 'Members', 'public', '<p><span class="embedded" data-embed="user/sidebar" data-label="User: Sidebar" title="Click to edit."></span><br></p>', 'no');

create sequence #prefix#user_id_seq;

create table #prefix#user (
	id integer not null default nextval('#prefix#user_id_seq') primary key,
	email varchar(72) unique not null,
	password varchar(128) not null,
	session_id varchar(32) unique,
	expires timestamp not null,
	name varchar(72) not null,
	type varchar(32) not null,
	signed_up timestamp not null,
	updated timestamp not null,
	userdata text not null,
	phone char(32) not null default '',
	fax char(32) not null default '',
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

insert into #prefix#user (id, email, password, session_id, expires, name, type, signed_up, updated, userdata) values (1, 'you@example.com', '$2a$07$1QeR9mu2doQxY0uBcpFlrOIfDxq0BwpR8FsImCgWvAL4Fz9jDByxi', null, now(), 'Admin User', 'admin', now(), now(), '[]');

create table #prefix#user_session (
	session_id varchar(32) not null primary key,
	expires timestamp not null,
	user_id integer not null
);

create index #prefix#user_session_user on #prefix#user_session (user_id, expires);
create index #prefix#user_session_expires on #prefix#user_session (expires);

create table #prefix#user_openid (
	token varchar(200) primary key,
	user_id integer not null
);

create sequence #prefix#user_links_seq;

create table #prefix#user_links (
	id integer not null default nextval('#prefix#user_links_seq') primary key,
	user_id int not null,
	service char(32) not null default '',
	handle char(72) not null
);

create index #prefix#user_links_user on #prefix#user_links (user_id, service);

create sequence #prefix#user_notes_seq;

create table #prefix#user_notes (
	id integer not null default nextval('#prefix#user_notes_seq') primary key,
	user_id int not null,
	ts timestamp not null,
	made_by int not null,
	note text not null
);

create index #prefix#user_notes_user on #prefix#user_notes (user_id, ts);
create index #prefix#user_notes_made_by on #prefix#user_notes (made_by, ts);

create sequence #prefix#versions_id_seq;

create table #prefix#versions (
	id integer not null default nextval('#prefix#versions_id_seq') primary key,
	class varchar(72) not null,
	pkey varchar(72) not null,
	"user" integer not null,
	ts timestamp not null,
	serialized text not null
);

create index #prefix#versions_class on #prefix#versions (class, pkey, ts);
create index #prefix#versions_user on #prefix#versions ("user", ts);

create table #prefix#api (
	token varchar(35) not null primary key,
	api_key varchar(35) not null,
	user_id integer not null
);

create index #prefix#api_token on #prefix#api (token, api_key);
create index #prefix#api_user on #prefix#api (user_id);

create sequence #prefix#blog_post_id_seq;

create table #prefix#blog_post (
	id integer not null default nextval('#prefix#blog_post_id_seq') primary key,
	title varchar(128) not null,
	ts timestamp not null,
	author varchar(32) not null,
	published varchar(3) not null,
	body text not null,
	tags text not null,
	extra text not null,
	thumbnail char(128) not null default '',
	check (published in ('yes', 'no', 'que'))
);

create index #prefix#blog_post_ts on #prefix#blog_post (ts);
create index #prefix#blog_post_pts on #prefix#blog_post (ts, published);

create table #prefix#blog_tag (
	id varchar(48) not null primary key
);

create table #prefix#blog_post_tag (
	tag_id varchar(48) not null,
	post_id integer not null,
	primary key (tag_id, post_id)
);

create sequence #prefix#lock_id_seq;

create table #prefix#lock (
	id integer not null default nextval('#prefix#lock_id_seq') primary key,
	"user" integer not null,
	resource varchar(72) not null,
	resource_id varchar(72) not null,
	expires timestamp not null,
	created timestamp not null,
	modified timestamp not null
);

create index #prefix#lock_resource on #prefix#lock (resource, resource_id, expires);
create index #prefix#lock_user on #prefix#lock ("user");

create table #prefix#filemanager_prop (
	file char(128) not null,
	prop char(32) not null,
	value char(255) not null,
	primary key (file, prop)
);

create table #prefix#apps (
	name varchar(48) not null primary key,
	version varchar(16) not null
);

insert into #prefix#apps (name, version) values ('elefant', '1.3.12');
insert into #prefix#apps (name, version) values ('blog', '1.1.4-stable');
insert into #prefix#apps (name, version) values ('user', '1.1.5-stable');
insert into #prefix#apps (name, version) values ('filemanager', '1.3.0-beta');

create sequence #prefix#extended_fields_seq;

create table #prefix#extended_fields (
	id integer not null default nextval('#prefix#extended_fields_seq') primary key,
	class char(48) not null,
	sort int not null,
	name char(48) not null,
	label char(48) not null,
	type char(24) not null,
	required int not null,
	options char(255) not null
);

create index #prefix#extended_fields_class on #prefix#extended_fields (class, sort);

commit;
