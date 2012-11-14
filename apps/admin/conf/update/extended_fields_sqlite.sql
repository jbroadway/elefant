create table #prefix#extended_fields (
	id integer primary key,
	class char(48) not null,
	sort int not null,
	name char(48) not null,
	label char(48) not null,
	type char(24) not null,
	required int not null,
	options char(255) not null
);

create index #prefix#extended_fields_class on #prefix#extended_fields (class, sort);
