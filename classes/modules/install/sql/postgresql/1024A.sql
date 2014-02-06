alter table "station" add column work_code_definition character varying;
create index system_log_user_id_date on system_log(user_id,date);

