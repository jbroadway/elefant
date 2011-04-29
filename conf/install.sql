create table webpage (
	id char(72) not null primary key,
	title char(72) not null,
	template char(48) not null,
	head text,
	body text
);
