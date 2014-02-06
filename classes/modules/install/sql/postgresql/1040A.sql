CREATE TABLE user_report_data (
    id serial NOT NULL,
    company_id integer NOT NULL,
    user_id integer,
    script character varying NOT NULL,
    name character varying NOT NULL,
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
CREATE UNIQUE INDEX user_report_data_id ON user_generic_data USING btree (id);
CREATE INDEX user_report_data_company_id ON user_generic_data USING btree (company_id);
CREATE INDEX user_report_data_user_id ON user_generic_data USING btree (user_id);


DROP INDEX user_generic_status_id;
CREATE UNIQUE INDEX user_generic_status_id ON user_generic_status USING btree (id);
DROP INDEX user_identification_id;
CREATE UNIQUE INDEX user_identification_id ON user_identification USING btree (id);
DROP INDEX wage_group_id;
CREATE UNIQUE INDEX wage_group_id ON wage_group USING btree (id);
DROP INDEX company_generic_map_id;
CREATE UNIQUE INDEX company_generic_map_id ON company_generic_map USING btree (id);
DROP INDEX system_log_detail_id;
CREATE UNIQUE INDEX system_log_detail_id ON system_log_detail USING btree (id);
DROP INDEX message_recipient_id;
CREATE UNIQUE INDEX message_recipient_id ON message_recipient USING btree (id);
DROP INDEX message_sender_id;
CREATE UNIQUE INDEX message_sender_id ON message_sender USING btree (id);
DROP INDEX message_control_id;
CREATE UNIQUE INDEX message_control_id ON message_control USING btree (id);

CREATE INDEX system_log_object_id_table_name ON system_log USING btree (object_id,table_name);

ALTER TABLE permission_control ADD COLUMN level smallint DEFAULT 1;
