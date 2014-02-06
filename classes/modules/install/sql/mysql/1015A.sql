alter table station add column branch_id integer;
alter table station add column department_id integer;
alter table station add column time_zone varchar(250);

alter table station add column user_group_selection_type_id smallint;
alter table station add column branch_selection_type_id smallint;
alter table station add column department_selection_type_id smallint;

alter table station add column port integer;
alter table station add column user_name varchar(250);
alter table station add column password varchar(250);
alter table station add column poll_frequency integer;
alter table station add column push_frequency integer;
alter table station add column last_punch_time_stamp timestamp;
alter table station add column last_poll_date integer;
alter table station add column last_poll_status_message varchar(250);
alter table station add column last_push_date integer;
alter table station add column last_push_status_message varchar(250);

alter table station add column user_value_1 varchar(250);
alter table station add column user_value_2 varchar(250);
alter table station add column user_value_3 varchar(250);
alter table station add column user_value_4 varchar(250);
alter table station add column user_value_5 varchar(250);

update station set user_group_selection_type_id = 20;
update station set branch_selection_type_id = 20;
update station set department_selection_type_id = 20;

CREATE TABLE station_branch (
    id serial NOT NULL,
	station_id integer NOT NULL,
	branch_id integer NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE station_department (
    id serial NOT NULL,
	station_id integer NOT NULL,
	department_id integer NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE station_user_group (
    id serial NOT NULL,
	station_id integer NOT NULL,
	group_id integer NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE station_include_user (
    id serial NOT NULL,
	station_id integer NOT NULL,
	user_id integer NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE station_exclude_user (
    id serial NOT NULL,
	station_id integer NOT NULL,
	user_id integer NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE INDEX station_branch_station_id ON station_branch(station_id);
CREATE INDEX station_department_station_id ON station_department(station_id);
CREATE INDEX station_user_group_station_id ON station_user_group(station_id);
CREATE INDEX station_include_user_station_id ON station_include_user(station_id);
CREATE INDEX station_exclude_user_station_id ON station_exclude_user(station_id);