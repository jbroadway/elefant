alter table #prefix#blog_post add column slug char(128) not null default '';
alter table #prefix#blog_post add column description text not null default '';
alter table #prefix#blog_post add column keywords text not null default '';
