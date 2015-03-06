CREATE TABLE user_report_data (
	id serial NOT NULL, 
	company_id integer NOT NULL,
	user_id integer,
	script varchar(250) NOT NULL,
	is_default smallint DEFAULT 0 NOT NULL,
	description text,
	data text,
	created_date integer,
	created_by integer,	 
	updated_date integer,
	updated_by integer,	 
	deleted_date integer,
	deleted_by integer,	 
	deleted smallint DEFAULT 0 NOT NULL 
);
CREATE UNIQUE INDEX user_report_data_id ON user_generic_data(id);
CREATE INDEX user_report_data_company_id ON user_generic_data(company_id);
CREATE INDEX user_report_data_user_id ON user_generic_data(user_id);

CREATE INDEX system_log_object_id_table_name ON system_log(object_id,table_name);

ALTER TABLE permission_control ADD COLUMN level smallint DEFAULT 1;
