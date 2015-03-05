ALTER TABLE punch ADD COLUMN position_accuracy integer;
ALTER TABLE punch ADD COLUMN has_image smallint;

ALTER TABLE user_preference ADD COLUMN enable_email_notification_pay_stub smallint DEFAULT 1;
ALTER TABLE user_default ADD COLUMN enable_email_notification_pay_stub smallint DEFAULT 1;

CREATE INDEX punch_has_image ON punch USING btree (has_image);
CREATE INDEX "user_deduction_company_deduction_id" ON "user_deduction" USING btree (company_deduction_id);
CREATE INDEX "company_deduction_company_id" ON "company_deduction" USING btree (company_id);
CREATE INDEX "recurring_holiday_company_id" ON "recurring_holiday" USING btree (company_id);
CREATE INDEX "accrual_balance_user_id" ON "accrual_balance" USING btree (user_id);
CREATE INDEX permission_user_permission_control_id ON permission_user USING btree (permission_control_id);
CREATE INDEX permission_user_user_id ON permission_user USING btree (user_id);

delete from user_preference where id in ( select a.id from ( select user_id from user_preference group by user_id having count(*) > 1 ) as tmp, user_preference as a WHERE a.user_id = tmp.user_id );
CREATE UNIQUE INDEX "user_preference_user_id_ukey" ON "user_preference" USING btree (user_id);

ALTER TABLE permission cluster ON permission_permission_control_id;
ALTER TABLE permission_control cluster ON permission_control_company_id;

ALTER TABLE exception CLUSTER ON exception_user_date_id;
ALTER TABLE pay_stub_entry CLUSTER ON pay_stub_entry_pay_stub_id;
ALTER TABLE pay_stub_entry_account CLUSTER ON pay_stub_entry_account_company_id;
ALTER TABLE pay_stub CLUSTER ON pay_stub_user_id;
ALTER TABLE pay_stub_amendment CLUSTER ON pay_stub_amendment_user_id;

ALTER TABLE punch_control CLUSTER ON punch_control_user_date_id;
ALTER TABLE punch CLUSTER ON punch_punch_control_id;

ALTER TABLE schedule CLUSTER ON schedule_user_date_id;

ALTER TABLE system_log_detail CLUSTER ON system_log_detail_system_log_id;
ALTER TABLE system_log CLUSTER ON system_log_user_id_date;

ALTER TABLE user_date_total CLUSTER ON user_date_total_user_date_id;
ALTER TABLE user_date CLUSTER ON user_date_user_id;

ALTER TABLE station CLUSTER ON station_company_id_status_id_type_id;

ALTER TABLE accrual CLUSTER ON accrual_user_id;
ALTER TABLE accrual_balance CLUSTER ON accrual_balance_user_id;

ALTER TABLE message_control CLUSTER ON message_control_object_type_id_object_id;
ALTER TABLE message_sender CLUSTER ON message_sender_message_control_id;
ALTER TABLE message_recipient CLUSTER ON message_recipient_message_sender_id;

ALTER TABLE company_user_count CLUSTER ON company_user_count_company_id_date_stamp;
ALTER TABLE company_generic_map CLUSTER ON company_generic_map_company_id_object_type_id_object_id;

ALTER TABLE user_identification CLUSTER ON user_identification_user_id;
ALTER TABLE user_generic_data CLUSTER ON user_generic_data_user_id;
ALTER TABLE user_generic_status CLUSTER ON user_generic_status_user_id_batch_id;
ALTER TABLE users CLUSTER ON users_company_id;
ALTER TABLE user_preference CLUSTER ON user_preference_user_id_ukey;

ALTER TABLE request CLUSTER ON request_user_date_id;

ALTER TABLE pay_period CLUSTER ON pay_period_pay_period_schedule_id;
ALTER TABLE pay_period_schedule_user CLUSTER ON pay_period_schedule_user_pay_period_schedule_id;
ALTER TABLE pay_period_time_sheet_verify CLUSTER ON pay_period_time_sheet_verify_user_id_pay_period_id;

ALTER TABLE user_deduction CLUSTER ON user_deduction_company_deduction_id;
ALTER TABLE company_deduction CLUSTER ON company_deduction_company_id;

ALTER TABLE exception_policy CLUSTER ON exception_policy_active_type_id;
ALTER TABLE recurring_holiday CLUSTER ON recurring_holiday_company_id;
ALTER TABLE holidays CLUSTER ON holidays_holiday_policy_id;
ALTER TABLE policy_group_user CLUSTER ON policy_group_user_policy_group_id;

