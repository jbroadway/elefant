begin;

create table #prefix#webpage (
	id char(72) not null primary key,
	title char(72) not null,
	menu_title char(72) not null,
	window_title char(72) not null,
	access enum('public','member','private') not null default 'public',
	layout char(48) not null,
	description text,
	keywords text,
	body text,
	extra text,
	index (access)
) default charset=utf8;

insert into #prefix#webpage (id, title, menu_title, window_title, access, layout, description, keywords, body, extra) values ('index', 'Welcome to Elefant', 'Home', '', 'public', 'default', '', '', '<table><tbody><tr><td><h3>Congratulations!</h3>You have successfully installed Elefant, the refreshingly simple new PHP web framework and CMS.</td><td><h3>Getting Started</h3>To log in as an administrator and edit pages, write a blog post, or upload files, go to <a href="/admin">/admin</a>.</td><td><h3>Developers</h3>Documentation, source code and issue tracking can be found at <a href="http://github.com/jbroadway/elefant">github.com/jbroadway/elefant</a></td></tr></tbody></table>', '[]');

create table #prefix#block (
	id char(72) not null primary key,
	title char(72) not null,
	body text,
	access enum('public','member','private') not null default 'public',
	show_title enum('yes','no') not null default 'yes',
	index (access)
) default charset=utf8;

insert into #prefix#block (id, title, access, body, show_title) values ('members', 'Members', 'public', '<p><span class="embedded" data-embed="user/sidebar" data-label="User: Sidebar" title="Click to edit."></span><br></p>', 'no');

create table #prefix#user (
	id int not null auto_increment primary key,
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
	about text not null,
	website char(128) not null default '',
	index(email, password),
	index(session_id)
) default charset=utf8;

insert into #prefix#user (id, email, password, session_id, expires, name, type, signed_up, updated, userdata) values (1, 'you@example.com', '$2a$07$1QeR9mu2doQxY0uBcpFlrOIfDxq0BwpR8FsImCgWvAL4Fz9jDByxi', null, now(), 'Admin User', 'admin', now(), now(), '[]');

create table #prefix#user_session (
	session_id char(32) not null primary key,
	expires datetime not null,
	user_id int not null,
	index (user_id, expires),
	index (expires)
);

create table #prefix#user_openid (
	token char(200) primary key,
	user_id int not null
) default charset=utf8;

create table #prefix#user_links (
	id int not null auto_increment primary key,
	user_id int not null,
	service char(32) not null default '',
	handle char(72) not null,
	index (user_id, service)
);

create table #prefix#user_notes (
	id int not null auto_increment primary key,
	user_id int not null,
	ts datetime not null,
	made_by int not null,
	note text not null,
	index (user_id, ts),
	index (made_by, ts)
);

create table #prefix#versions (
	id int not null auto_increment primary key,
	class char(72) not null,
	pkey char(72) not null,
	user int not null,
	ts datetime not null,
	serialized text not null,
	index (class, pkey, ts),
	index (user, ts)
) default charset=utf8;

create table #prefix#api (
	token char(35) not null primary key,
	api_key char(35) not null,
	user_id int not null,
	index (token, api_key),
	index (user_id)
) default charset=utf8;

create table #prefix#blog_post (
	id int not null auto_increment primary key,
	title char(128) not null,
	ts datetime not null,
	author char(32) not null,
	published enum('yes','no','que') not null,
	body text not null,
	tags text not null,
	extra text not null,
	thumbnail char(128) not null default '',
	index (ts),
	index (ts, published)
) default charset=utf8;

create table #prefix#blog_tag (
	id char(48) not null primary key
) default charset=utf8;

create table #prefix#blog_post_tag (
	tag_id char(48) not null,
	post_id int not null,
	primary key (tag_id, post_id)
) default charset=utf8;

create table `#prefix#lock` (
	id int not null auto_increment primary key,
	user int not null,
	resource varchar(72) not null,
	resource_id varchar(72) not null,
	expires datetime not null,
	created datetime not null,
	modified datetime not null,
	index (user),
	index (resource, resource_id, expires)
) default charset=utf8;

create table #prefix#filemanager_prop (
	file char(128) not null,
	prop char(32) not null,
	value char(255) not null,
	primary key (file, prop)
) default charset=utf8;

create table #prefix#apps (
	name char(48) not null primary key,
	version char(16) not null
) default charset=utf8;

insert into #prefix#apps (name, version) values ('elefant', '1.3.12');
insert into #prefix#apps (name, version) values ('blog', '1.1.4-stable');
insert into #prefix#apps (name, version) values ('user', '1.1.5-stable');
insert into #prefix#apps (name, version) values ('filemanager', '1.3.0-beta');

create table #prefix#extended_fields (
	id int not null primary key auto_increment,
	class char(48) not null,
	sort int not null,
	name char(48) not null,
	label char(48) not null,
	type char(24) not null,
	required int not null,
	options char(255) not null,
	index (class, sort)
);

commit;
