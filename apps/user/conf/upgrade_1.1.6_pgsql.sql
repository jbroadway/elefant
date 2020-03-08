alter table #prefix#api add column issued timestamp not null default CURRENT_TIMESTAMP;
alter table #prefix#api add column valid char(3) not null default 'yes';
alter table #prefix#api add check (valid in ('yes', 'no'));
alter table #prefix#api add index (issued);
