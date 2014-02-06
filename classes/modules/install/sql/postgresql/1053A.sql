ALTER TABLE premium_policy add column include_holiday_type_id integer default 10;
ALTER TABLE premium_policy add column maximum_daily_trigger_time integer default 0;
ALTER TABLE premium_policy add column maximum_weekly_trigger_time integer default 0;

CREATE INDEX company_user_count_company_id_date_stamp ON company_user_count USING btree (company_id,date_stamp);
