alter table break_policy add column include_multiple_breaks smallint;
update break_policy set include_multiple_breaks = 0;

