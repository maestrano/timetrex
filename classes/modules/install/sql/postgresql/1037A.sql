CREATE TABLE message_recipient (
	id serial NOT NULL,
	user_id integer NOT NULL,
	message_sender_id integer NOT NULL,
	status_id integer NOT NULL,
	status_date integer,
	ack smallint,
	ack_date integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);

CREATE TABLE message_sender (
	id serial NOT NULL,
	user_id integer NOT NULL,
	parent_id integer NOT NULL,
	message_control_id integer NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);

CREATE TABLE message_control (
	id serial NOT NULL,
	object_type_id integer NOT NULL,
	object_id integer NOT NULL,
	require_ack smallint DEFAULT 0 NOT NULL,
	priority_id smallint DEFAULT 0 NOT NULL,
	subject character varying,
	body character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);


CREATE INDEX message_recipient_user_id ON message_recipient USING btree (user_id);
CREATE INDEX message_recipient_message_sender_id ON message_recipient USING btree (message_sender_id);
CREATE INDEX message_sender_user_id ON message_sender USING btree (user_id);
CREATE INDEX message_sender_message_control_id ON message_sender USING btree (message_control_id);
CREATE INDEX message_control_object_type_id_object_id ON message_control USING btree (object_type_id,object_id);

ALTER TABLE pay_period_schedule ADD COLUMN timesheet_verify_type_id integer DEFAULT 10;
UPDATE pay_period_schedule set timesheet_verify_type_id = 40;
ALTER TABLE pay_period_time_sheet_verify ADD COLUMN user_verified smallint DEFAULT 0;
ALTER TABLE pay_period_time_sheet_verify ADD COLUMN user_verified_date integer;

ALTER TABLE company ADD COLUMN ldap_authentication_type_id smallint DEFAULT 0;
ALTER TABLE company ADD COLUMN ldap_host varchar(100);
ALTER TABLE company ADD COLUMN ldap_port integer DEFAULT 389;
ALTER TABLE company ADD COLUMN ldap_bind_user_name varchar(100);
ALTER TABLE company ADD COLUMN ldap_bind_password varchar(100);
ALTER TABLE company ADD COLUMN ldap_base_dn varchar(250);
ALTER TABLE company ADD COLUMN ldap_bind_attribute varchar(100);
ALTER TABLE company ADD COLUMN ldap_user_filter varchar(250);
ALTER TABLE company ADD COLUMN ldap_login_attribute varchar(100);
ALTER TABLE company ADD COLUMN ldap_group_dn varchar(250);
ALTER TABLE company ADD COLUMN ldap_group_user_attribute varchar(100);
ALTER TABLE company ADD COLUMN ldap_group_name varchar(100);
ALTER TABLE company ADD COLUMN ldap_group_attribute varchar(250);
