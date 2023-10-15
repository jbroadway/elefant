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
	thumbnail char(128) not null default '',
	index (access)
) engine=InnoDB default charset=utf8mb4;

insert into #prefix#webpage (id, title, menu_title, window_title, access, layout, description, keywords, body, extra) values ('index', 'Welcome to Elefant', 'Home', '', 'public', 'default', '', '', '<table><tbody><tr><td><h3>Congratulations!</h3>You have successfully installed Elefant, the refreshingly simple new PHP web framework and CMS.</td><td><h3>Getting Started</h3>To log in as an administrator and edit pages, write a blog post, or upload files, go to <a href="/admin">/admin</a>.</td><td><h3>Developers</h3>Documentation, source code and issue tracking can be found at <a href="http://github.com/jbroadway/elefant">github.com/jbroadway/elefant</a></td></tr></tbody></table>', '[]');

create table #prefix#block (
	id char(72) not null primary key,
	title char(72) not null,
	body text,
	access enum('public','member','private') not null default 'public',
	show_title enum('yes','no') not null default 'yes',
	background char(128) not null default '',
	style char(72) not null default '',
	column_layout char(24) not null default '100',
	col2 text,
	col3 text,
	col4 text,
	col5 text,
	index (access)
) engine=InnoDB default charset=utf8mb4;

create table #prefix#block_group_order (
	order_id char(191) not null primary key,
	sorting_order text
) engine=InnoDB default charset=utf8mb4;

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
) engine=InnoDB default charset=utf8mb4;

insert into #prefix#user (id, email, password, session_id, expires, name, type, signed_up, updated, userdata, about) values (1, 'you@example.com', '$2a$07$1QeR9mu2doQxY0uBcpFlrOIfDxq0BwpR8FsImCgWvAL4Fz9jDByxi', null, now(), 'Admin User', 'admin', now(), now(), '[]', '');

create table #prefix#user_session (
	session_id char(32) not null primary key,
	expires datetime not null,
	user_id int not null,
	index (user_id, expires),
	index (expires)
) engine=InnoDB default charset=utf8mb4;

create table #prefix#user_openid (
	token char(191) primary key,
	user_id int not null
) engine=InnoDB default charset=utf8mb4;

create table #prefix#user_links (
	id int not null auto_increment primary key,
	user_id int not null,
	service char(32) not null default '',
	handle char(72) not null,
	index (user_id, service)
) engine=InnoDB default charset=utf8mb4;

create table #prefix#user_notes (
	id int not null auto_increment primary key,
	user_id int not null,
	ts datetime not null,
	made_by int not null,
	note text not null,
	index (user_id, ts),
	index (made_by, ts)
) engine=InnoDB default charset=utf8mb4;

create table #prefix#versions (
	id int not null auto_increment primary key,
	class char(72) not null,
	pkey char(72) not null,
	user int not null,
	ts datetime not null,
	serialized text not null,
	index (class, pkey, ts),
	index (user, ts)
) engine=InnoDB default charset=utf8mb4;

create table #prefix#api (
	token char(35) not null primary key,
	api_key char(35) not null,
	user_id int not null,
	issued datetime not null,
	valid enum('yes','no') not null default 'yes',
	index (token, api_key, valid),
	index (user_id),
	index (issued)
) engine=InnoDB default charset=utf8mb4;

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
	slug char(128) not null default '',
	description text not null,
	keywords text not null,
	index (ts),
	index (ts, published)
) engine=InnoDB default charset=utf8mb4;

create table #prefix#blog_tag (
	id char(48) not null primary key
) engine=InnoDB default charset=utf8mb4;

create table #prefix#blog_post_tag (
	tag_id char(48) not null,
	post_id int not null,
	primary key (tag_id, post_id)
) engine=InnoDB default charset=utf8mb4;

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
) engine=InnoDB default charset=utf8mb4;

create table #prefix#filemanager_prop (
	file char(128) not null,
	prop char(32) not null,
	value char(191) not null,
	primary key (file, prop)
) engine=InnoDB default charset=utf8mb4;

create table #prefix#filemanager_bitly_link (
	link char(191) not null primary key,
	bitly_link char(30) not null
) engine=InnoDB default charset=utf8mb4;

create table #prefix#apps (
	name char(48) not null primary key,
	version char(16) not null
) engine=InnoDB default charset=utf8mb4;

insert into #prefix#apps (name, version) values ('elefant', '#ELEFANT_VERSION#');
insert into #prefix#apps (name, version) values ('blog', '#appconf.blog.Admin.version#');
insert into #prefix#apps (name, version) values ('user', '#appconf.user.Admin.version#');
insert into #prefix#apps (name, version) values ('filemanager', '#appconf.filemanager.Admin.version#');

create table #prefix#extended_fields (
	id int not null primary key auto_increment,
	class char(48) not null,
	sort int not null,
	name char(48) not null,
	label char(48) not null,
	type char(24) not null,
	required int not null,
	options char(191) not null,
	index (class, sort)
) engine=InnoDB default charset=utf8mb4;

CREATE TABLE #prefix#oauth_clients (
	client_id             VARCHAR(80)   NOT NULL,
	client_secret         VARCHAR(80),
	redirect_uri          VARCHAR(2000),
	grant_types           VARCHAR(80),
	scope                 VARCHAR(4000),
	user_id               VARCHAR(80),
	client_name           VARCHAR(80)   NOT NULL DEFAULT '',
	PRIMARY KEY (client_id)
);

CREATE TABLE #prefix#oauth_access_tokens (
	access_token         VARCHAR(40)    NOT NULL,
	client_id            VARCHAR(80)    NOT NULL,
	user_id              VARCHAR(80),
	expires              TIMESTAMP      NOT NULL,
	scope                VARCHAR(4000),
	PRIMARY KEY (access_token)
);

CREATE TABLE #prefix#oauth_authorization_codes (
	authorization_code  VARCHAR(40)     NOT NULL,
	client_id           VARCHAR(80)     NOT NULL,
	user_id             VARCHAR(80),
	redirect_uri        VARCHAR(2000),
	expires             TIMESTAMP       NOT NULL,
	scope               VARCHAR(4000),
	id_token            VARCHAR(1000),
	PRIMARY KEY (authorization_code)
);

CREATE TABLE #prefix#oauth_refresh_tokens (
	refresh_token       VARCHAR(40)     NOT NULL,
	client_id           VARCHAR(80)     NOT NULL,
	user_id             VARCHAR(80),
	expires             TIMESTAMP       NOT NULL,
	scope               VARCHAR(4000),
	PRIMARY KEY (refresh_token)
);

CREATE TABLE #prefix#oauth_scopes (
	scope               VARCHAR(80)     NOT NULL,
	is_default          BOOLEAN,
	PRIMARY KEY (scope)
);

CREATE TABLE #prefix#oauth_jwt (
	client_id           VARCHAR(80)     NOT NULL,
	subject             VARCHAR(80),
	public_key          VARCHAR(2000)   NOT NULL
);

CREATE TABLE #prefix#oauth_jti (
	issuer              VARCHAR(80)   NOT NULL,
	subject             VARCHAR(80),
	audiance            VARCHAR(80),
	expires             TIMESTAMP     NOT NULL,
	jti                 VARCHAR(2000) NOT NULL
);

CREATE TABLE #prefix#oauth_public_keys (
	client_id            VARCHAR(80),
	public_key           VARCHAR(2000),
	private_key          VARCHAR(2000),
	encryption_algorithm VARCHAR(100) DEFAULT 'RS256'
);

commit;
