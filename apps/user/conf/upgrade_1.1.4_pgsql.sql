create table #prefix#user_session (
	session_id varchar(32) not null primary key,
	expires timestampe not null,
	user_id integer not null
);

create index #prefix#user_session_user on #prefix#user_session (user_id, expires);
create index #prefix#user_session_expires on #prefix#user_session (expires);
