create table webpage (
	id char(72) not null primary key,
	title char(72) not null,
	menu_title char(72) not null,
	window_title char(72) not null,
	weight int not null,
	access enum('public','member','private') not null default 'public',
	layout char(48) not null,
	description text,
	keywords text,
	body text,
	index (access, weight)
);

insert into webpage (id, title, menu_title, window_title, weight, access, layout, description, keywords, body) values ('index', 'Welcome to Elefant', 'Home', '', 1, 'public', 'index', '', '', '<table><tbody><tr><td><h3>Congratulations!</h3>You have successfully installed Elefant, the refreshingly simple new PHP web framework and CMS.</td><td><h3>Getting Started</h3>To log in as an administrator and edit pages, write a blog post, or upload files, go to <a href="/admin">/admin</a>.</td><td><h3>Developers</h3>Documentation, source code and issue tracking can be found at <a href="http://github.com/jbroadway/elefant">github.com/jbroadway/elefant</a></td></tr></tbody></table>');
insert into webpage (id, title, menu_title, window_title, weight, access, layout, description, keywords, body) values ('blog', 'Blog', '', '', 0, 'public', 'default', '', '', '{! admin/forward?to=/blog !}');

create table block (
	id char(72) not null primary key,
	title char(72) not null,
	body text,
	access enum('public','member','private') not null default 'public',
	show_title enum('yes','no') not null default 'yes',
	index (access)
);

insert into block (id, title, access, body, show_title) values ('members', 'Members', 'public', '{! user/sidebar !}', 'no');

create table user (
	id int not null auto_increment primary key,
	email char(72) unique not null,
	password char(35) not null,
	session_id char(32) unique,
	expires datetime not null,
	name char(72) not null,
	type char(32) not null,
	signed_up datetime not null,
	updated datetime not null,
	userdata text not null,
	index(email, password),
	index(session_id)
);

create table versions (
	id int not null auto_increment primary key,
	class char(72) not null,
	pkey char(72) not null,
	user int not null,
	ts datetime not null,
	serialized text not null,
	index (class, pkey, ts),
	index (user, ts)
);

create table api (
	token char(35) not null primary key,
	api_key char(35) not null,
	user_id int not null,
	index (token, api_key),
	index (user_id)
);

create table blog_post (
	id int not null auto_increment primary key,
	title char(72) not null,
	ts datetime not null,
	author char(32) not null,
	published enum('yes','no') not null,
	body text not null,
	tags text not null,
	index (ts),
	index (ts, published)
);

create table blog_tag (
	id char(24) not null primary key
);

create table blog_post_tag (
	tag_id char(24) not null,
	post_id int not null,
	primary key (tag_id, post_id)
);

create table `lock` (
	id int not null auto_increment primary key,
	user int not null,
	resource varchar(72) not null,
	resource_id varchar(72) not null,
	expires datetime not null,
	created datetime not null,
	modified datetime not null,
	index (user),
	index (resource, resource_id, expires)
);

create table apps (
	name char(48) not null primary key,
	version char(16) not null
);
