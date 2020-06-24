alter table #prefix#block add column background char(128) not null default '';

create table #prefix#block_group_order (
	order_id char(255) not null primary key,
	sorting_order text
);
