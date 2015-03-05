alter table company change originagor_id originator_id varchar(250);
CREATE INDEX holidays_date_stamp ON holidays(date_stamp);
CREATE INDEX schedule_user_date_id ON schedule(user_date_id);

