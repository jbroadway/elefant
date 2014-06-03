alter table #prefix#user add column phone char(32) not null default '';
alter table #prefix#user add column address char(48) not null default '';
alter table #prefix#user add column address2 char(48) not null default '';
alter table #prefix#user add column city char(48) not null default '';
alter table #prefix#user add column state char(3) not null default '';
alter table #prefix#user add column country char(3) not null default '';
alter table #prefix#user add column zip char(16) not null default '';
alter table #prefix#user add column title char(48) not null default '';
alter table #prefix#user add column company char(48) not null default '';
alter table #prefix#user add column photo char(128) not null default '';
alter table #prefix#user add column about text not null default '';
alter table #prefix#user add column website char(128) not null default '';

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
