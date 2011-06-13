create table webpage (
	id char(72) not null primary key,
	title char(72) not null,
	menu_title char(72) not null,
	window_title char(72) not null,
	weight int not null,
	access char(12) not null,
	layout char(48) not null,
	description text,
	keywords text,
	body text
);

create index webpage_access on webpage (access, weight);

insert into webpage (id, title, menu_title, window_title, weight, access, layout, description, keywords, body) values ('index', 'Congratulations!', 'Home', 'Home', 0, 'public', '', '', '', '<p>You have successfully installed Elefant!</p>');

create table user (
	id integer primary key,
	email char(72) unique not null,
	password char(35) not null,
	session_id char(32) unique,
	expires datetime not null,
	name char(72) not null,
	type char(32) not null,
	signed_up datetime not null,
	updated datetime not null,
	userdata text not null
);

create index user_email_password on user (email, password);
create index user_session_id on user (session_id);

create table versions (
	id integer primary key,
	class char(72) not null,
	pkey char(72) not null,
	user int not null,
	ts datetime not null,
	serialized text not null
);

create index versions_class on versions (class, pkey, ts);
create index versions_user on versions (user, ts);

create table api (
	token char(35) not null primary key,
	api_key char(35) not null,
	user_id int not null
);

create index api_token on api (token, api_key);
create index api_user on api (user_id);
