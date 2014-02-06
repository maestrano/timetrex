alter table company rename column "originagor_id" to "originator_id";
CREATE INDEX holidays_date_stamp ON holidays USING btree (date_stamp);
CREATE INDEX schedule_user_date_id ON schedule USING btree (user_date_id);
