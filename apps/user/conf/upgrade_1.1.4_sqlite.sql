create table #prefix#user_session (
	session_id char(32) not null primary key,
	expires datetime not null,
	user_id int not null
);

create index #prefix#user_session_user on #prefix#user_session (user_id, expires);
create index #prefix#user_session_expires on #prefix#user_session (expires);
