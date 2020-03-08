create table #prefix#api_tmp (
	token char(35) not null primary key,
	api_key char(35) not null,
	user_id int not null,
	issued datetime not null,
	valid char(3) not null default 'yes'
);

insert into #prefix#api_tmp select *, CURRENT_TIMESTAMP, 'yes' from #prefix#api;
drop table #prefix#api;
alter table #prefix#api_tmp rename to #prefix#api;

create index #prefix#api_token on #prefix#api (token, api_key, valid);
create index #prefix#api_user on #prefix#api (user_id);
create index #prefix#api_issued on #prefix#api (issued);
