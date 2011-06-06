create table webpage (
	id char(72) not null primary key,
	title char(72) not null,
	menu_title char(72) not null,
	window_title char(72) not null,
	weight int not null,
	layout char(48) not null,
	description text,
	keywords text,
	body text
);

insert into webpage (id, title, menu_title, window_title, weight, layout, description, keywords, body) values ('index', 'Congratulations!', 'Home', 'Home', 0, '', '', '', '<p>You have successfully installed PinkElefant!</p>');

create table user (
	id int not null primary key,
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
