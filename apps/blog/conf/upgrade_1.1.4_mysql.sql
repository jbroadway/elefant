alter table #prefix#blog_post add column thumbnail char(128) not null default '';
alter table #prefix#blog_post change column published published enum('yes','no','que') not null;
