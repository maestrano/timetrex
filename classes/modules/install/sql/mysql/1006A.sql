CREATE TABLE user_generic_status (
	id integer AUTO_INCREMENT NOT NULL,
	user_id integer NOT NULL,
	batch_id integer NOT NULL,
	status_id integer NOT NULL,
	label varchar(1024),
	description varchar(1024),
	link varchar(1024),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE UNIQUE INDEX user_generic_status_id ON user_generic_status(id);
CREATE INDEX user_generic_status_user_id_batch_id ON user_generic_status(user_id,batch_id);

alter table punch_control add column other_id1 varchar(255);
alter table punch_control add column other_id2 varchar(255);
alter table punch_control add column other_id3 varchar(255);
alter table punch_control add column other_id4 varchar(255);
alter table punch_control add column other_id5 varchar(255);

alter table punch_control add column note varchar(1024);

alter table user_default add column start_week_day integer;

