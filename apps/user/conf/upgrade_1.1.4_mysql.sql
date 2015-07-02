create table #prefix#user_session (
	session_id char(32) not null primary key,
	expires datetime not null,
	user_id int not null,
	index (user_id, expires),
	index (expires)
);
