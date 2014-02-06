drop sequence if exists permission_id_seq cascade;
drop sequence if exists permission_id_seq1 cascade;
drop table if exists permission_old;
create sequence permission_id_seq; 
select setval('permission_id_seq', (select max(id)+1000 from permission) );
alter table permission alter column id set default nextval('permission_id_seq');
update permission set id = nextval('permission_id_seq') where id in (select id from permission group by id having count(*) > 1);
create unique index permission_id on permission(id);
