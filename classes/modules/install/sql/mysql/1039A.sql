CREATE TABLE system_log_detail (
        id serial NOT NULL,
        system_log_id integer NOT NULL,
	field varchar(75),
	new_value text,
	old_value text
);
CREATE INDEX system_log_detail_system_log_id ON system_log_detail(system_log_id);
