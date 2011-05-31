create table blog_post (
	id int not null auto_increment primary key,
	title char(72) not null,
	ts datetime not null,
	author char(32) not null,
	body text not null,
	index (ts)
);
