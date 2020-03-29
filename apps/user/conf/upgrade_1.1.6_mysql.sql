alter table #prefix#api add column issued datetime not null;
alter table #prefix#api add column valid enum('yes','no') not null default 'yes';
alter table #prefix#api add index (issued);
