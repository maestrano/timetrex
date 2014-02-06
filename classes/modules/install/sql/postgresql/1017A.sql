alter table "users" add column rf_id integer;
alter table "users" add column rf_id_updated_date integer;
alter table "users" add column finger_print_1_updated_date integer;
alter table "users" add column finger_print_2_updated_date integer;
alter table "users" add column finger_print_3_updated_date integer;
alter table "users" add column finger_print_4_updated_date integer;

alter table "station" add column partial_push_frequency integer;
alter table "station" add column last_partial_push_date integer;
alter table "station" add column last_partial_push_status_message character varying;

alter table "station" add column pull_start_time timestamp with time zone;
alter table "station" add column pull_end_time timestamp with time zone;
alter table "station" add column push_start_time timestamp with time zone;
alter table "station" add column push_end_time timestamp with time zone;
alter table "station" add column partial_push_start_time timestamp with time zone;
alter table "station" add column partial_push_end_time timestamp with time zone;

alter table "branch" add column manual_id integer;
alter table "department" add column manual_id integer;
update branch set manual_id = id;
update department set manual_id = id;