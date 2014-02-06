alter table "station" add column branch_id integer;
alter table "station" add column department_id integer;
alter table "station" add column time_zone character varying;

alter table "station" add column user_group_selection_type_id smallint;
alter table "station" add column branch_selection_type_id smallint;
alter table "station" add column department_selection_type_id smallint;

alter table "station" add column port integer;
alter table "station" add column user_name character varying;
alter table "station" add column password character varying;
alter table "station" add column poll_frequency integer;
alter table "station" add column push_frequency integer;
alter table "station" add column last_punch_time_stamp timestamp with time zone;
alter table "station" add column last_poll_date integer;
alter table "station" add column last_poll_status_message character varying;
alter table "station" add column last_push_date integer;
alter table "station" add column last_push_status_message character varying;

alter table "station" add column user_value_1 character varying;
alter table "station" add column user_value_2 character varying;
alter table "station" add column user_value_3 character varying;
alter table "station" add column user_value_4 character varying;
alter table "station" add column user_value_5 character varying;

--Deny all employees by default
update station set user_group_selection_type_id = 20;
update station set branch_selection_type_id = 20;
update station set department_selection_type_id = 20;

CREATE TABLE station_branch (
    id serial NOT NULL,
	station_id integer NOT NULL,
	branch_id integer NOT NULL
);

CREATE TABLE station_department (
    id serial NOT NULL,
	station_id integer NOT NULL,
	department_id integer NOT NULL
);

CREATE TABLE station_user_group (
    id serial NOT NULL,
	station_id integer NOT NULL,
	group_id integer NOT NULL
);

CREATE TABLE station_include_user (
    id serial NOT NULL,
	station_id integer NOT NULL,
	user_id integer NOT NULL
);
CREATE TABLE station_exclude_user (
    id serial NOT NULL,
	station_id integer NOT NULL,
	user_id integer NOT NULL
);

CREATE INDEX station_branch_station_id ON station_branch USING btree (station_id);
CREATE INDEX station_department_station_id ON station_department USING btree (station_id);
CREATE INDEX station_user_group_station_id ON station_user_group USING btree (station_id);
CREATE INDEX station_include_user_station_id ON station_include_user USING btree (station_id);
CREATE INDEX station_exclude_user_station_id ON station_exclude_user USING btree (station_id);