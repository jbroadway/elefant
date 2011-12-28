begin;

create table webpage (
	id varchar(72) not null primary key,
	title varchar(72) not null,
	menu_title varchar(72) not null,
	window_title varchar(72) not null,
	access varchar(10) not null default 'public',
	layout varchar(48) not null,
	description text,
	keywords text,
	body text,
	check (access in ('public','member','private'))
);

create index webpage_access on webpage (access);

insert into webpage (id, title, menu_title, window_title, access, layout, description, keywords, body) values ('index', 'Welcome to Elefant', 'Home', '', 'public', 'index', '', '', '<table><tbody><tr><td><h3>Congratulations!</h3>You have successfully installed Elefant, the refreshingly simple new PHP web framework and CMS.</td><td><h3>Getting Started</h3>To log in as an administrator and edit pages, write a blog post, or upload files, go to <a href="/admin">/admin</a>.</td><td><h3>Developers</h3>Documentation, source code and issue tracking can be found at <a href="http://github.com/jbroadway/elefant">github.com/jbroadway/elefant</a></td></tr></tbody></table>');


create table block (
	id varchar(72) not null primary key,
	title varchar(72) not null,
	body text,
	access varchar(10) not null default 'public',
	show_title varchar(3) not null default 'yes',
	check (access in ('public','member','private')),
	check (show_title in ('yes','no'))
);

create index block_access on block (id, access);

insert into block (id, title, access, body, show_title) values ('members', 'Members', 'public', '{! user/sidebar !}', 'no');

create sequence user_id_seq;

create table "user" (
	id integer not null default nextval('user_id_seq') primary key,
	email varchar(72) unique not null,
	password varchar(35) not null,
	session_id varchar(32) unique,
	expires timestamp not null,
	name varchar(72) not null,
	type varchar(32) not null,
	signed_up timestamp not null,
	updated timestamp not null,
	userdata text not null
);

create index user_email_password on "user" (email, password);
create index user_session_id on "user" (session_id);

create table user_openid (
	token varchar(128) primary key,
	user_id integer not null
);

create sequence versions_id_seq;

create table versions (
	id integer not null default nextval('versions_id_seq') primary key,
	class varchar(72) not null,
	pkey varchar(72) not null,
	"user" integer not null,
	ts timestamp not null,
	serialized text not null
);

create index versions_class on versions (class, pkey, ts);
create index versions_user on versions ("user", ts);

create table api (
	token varchar(35) not null primary key,
	api_key varchar(35) not null,
	user_id integer not null
);

create index api_token on api (token, api_key);
create index api_user on api (user_id);

create sequence blog_post_id_seq;

create table blog_post (
	id integer not null default nextval('blog_post_id_seq') primary key,
	title varchar(72) not null,
	ts timestamp not null,
	author varchar(32) not null,
	published varchar(3) not null,
	body text not null,
	tags text not null,
	check (published in ('yes', 'no'))
);

create index blog_post_ts on blog_post (ts);
create index blog_post_pts on blog_post (ts, published);

create table blog_tag (
	id varchar(24) not null primary key
);

create table blog_post_tag (
	tag_id varchar(24) not null,
	post_id integer not null,
	primary key (tag_id, post_id)
);

create sequence lock_id_seq;

create table lock (
	id integer not null default nextval('lock_id_seq') primary key,
	"user" integer not null,
	resource varchar(72) not null,
	resource_id varchar(72) not null,
	expires timestamp not null,
	created timestamp not null,
	modified timestamp not null
);

create index lock_resource on lock (resource, resource_id, expires);
create index lock_user on lock ("user");

create table apps (
	name varchar(48) not null primary key,
	version varchar(16) not null
);

commit;
