create table blog_post (
	id int not null autoincrement primary key,
	title char(72) not null,
	ts datetime not null,
	author char(32) not null,
	body text not null
);

create index blog_post_ts on blog_post (ts);
