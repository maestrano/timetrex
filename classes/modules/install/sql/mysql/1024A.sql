alter table station add column work_code_definition varchar(250);
create index system_log_user_id_date on system_log(user_id,date);
