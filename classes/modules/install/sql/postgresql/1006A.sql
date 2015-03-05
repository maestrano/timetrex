CREATE TABLE user_generic_status (
	id serial NOT NULL,
	user_id integer NOT NULL,
	batch_id integer NOT NULL,
	status_id integer NOT NULL,
	label character varying,
	description character varying,
	link character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);
CREATE INDEX user_generic_status_id ON user_generic_status USING btree (id);
CREATE INDEX user_generic_status_user_id_batch_id ON user_generic_status USING btree (user_id,batch_id);

alter table "punch_control" add column other_id1 character varying;
alter table "punch_control" add column other_id2 character varying;
alter table "punch_control" add column other_id3 character varying;
alter table "punch_control" add column other_id4 character varying;
alter table "punch_control" add column other_id5 character varying;

alter table "punch_control" add column note character varying;

alter table "user_default" add column start_week_day integer;