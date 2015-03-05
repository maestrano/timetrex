CREATE TABLE system_log_detail (
		id serial NOT NULL,
		system_log_id integer NOT NULL,
	field varchar(75),
	new_value text,
	old_value text
);
CREATE INDEX system_log_detail_id ON system_log_detail USING btree (id);
CREATE INDEX system_log_detail_system_log_id ON system_log_detail USING btree (system_log_id);

CREATE INDEX message_recipient_id ON message_recipient USING btree (id);
CREATE INDEX message_sender_id ON message_sender USING btree (id);
CREATE INDEX message_control_id ON message_control USING btree (id);

