--Put partial unique index on punch table to prevent duplicate punches being assigned to a single punch_control row;
CREATE UNIQUE INDEX punch_punch_control_status_id on punch(punch_control_id,status_id) WHERE deleted = 0;

CREATE TABLE currency_rate (
	id serial NOT NULL,
	currency_id integer NOT NULL,
	date_stamp date NOT NULL,
	conversion_rate numeric(18,10) NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX "currency_rate_id" ON "currency_rate" USING btree (id);
CREATE INDEX "currency_rate_currency_id_date_stamp" ON "currency_rate" USING btree (currency_id, date_stamp);
CLUSTER "currency_rate" USING "currency_rate_currency_id_date_stamp";

ALTER TABLE users ADD COLUMN work_email_is_valid smallint DEFAULT 1;
ALTER TABLE users ADD COLUMN work_email_is_valid_key character varying;
ALTER TABLE users ADD COLUMN work_email_is_valid_date integer;
ALTER TABLE users ADD COLUMN home_email_is_valid smallint DEFAULT 1;
ALTER TABLE users ADD COLUMN home_email_is_valid_key character varying;
ALTER TABLE users ADD COLUMN home_email_is_valid_date integer;

ALTER TABLE users DROP COLUMN ibutton_id;
ALTER TABLE users DROP COLUMN finger_print_1;
ALTER TABLE users DROP COLUMN finger_print_2;
ALTER TABLE users DROP COLUMN finger_print_3;
ALTER TABLE users DROP COLUMN finger_print_4;
ALTER TABLE users DROP COLUMN rf_id;
ALTER TABLE users DROP COLUMN rf_id_updated_date;
ALTER TABLE users DROP COLUMN finger_print_1_updated_date;
ALTER TABLE users DROP COLUMN finger_print_2_updated_date;
ALTER TABLE users DROP COLUMN finger_print_3_updated_date;
ALTER TABLE users DROP COLUMN finger_print_4_updated_date;
