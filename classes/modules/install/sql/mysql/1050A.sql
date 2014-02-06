delete from user_report_data;
ALTER TABLE user_report_data ADD COLUMN name varchar(250) NOT NULL;

CREATE INDEX pay_period_pay_period_schedule_id ON pay_period(pay_period_schedule_id);
CREATE INDEX pay_stub_entry_account_company_id ON pay_stub_entry_account(company_id);
CREATE INDEX policy_group_user_user_id ON policy_group_user(user_id);
CREATE INDEX pay_period_schedule_user_user_id ON pay_period_schedule_user(user_id);
CREATE INDEX income_tax_rate_us_state_district_status ON income_tax_rate_us(state,district,status);
CREATE INDEX users_company_id ON users(company_id);
CREATE INDEX schedule_start_time ON schedule(start_time);
CREATE INDEX request_status_id_authorized ON request(status_id,authorized);
DROP INDEX recurring_schedule_user_id ON recurring_schedule_user;
CREATE INDEX recurring_schedule_id ON recurring_schedule_user(id);
CREATE INDEX recurring_schedule_recurring_schedule_control_id ON recurring_schedule_user(recurring_schedule_control_id);
CREATE INDEX recurring_schedule_user_id ON recurring_schedule_user(user_id);
