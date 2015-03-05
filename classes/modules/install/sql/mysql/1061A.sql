ALTER TABLE punch ADD COLUMN position_accuracy integer;
ALTER TABLE punch ADD COLUMN has_image smallint;

ALTER TABLE user_preference ADD COLUMN enable_email_notification_pay_stub smallint DEFAULT 1;
ALTER TABLE user_default ADD COLUMN enable_email_notification_pay_stub smallint DEFAULT 1;

CREATE INDEX punch_has_image ON punch(has_image);
CREATE INDEX user_deduction_company_deduction_id ON user_deduction(company_deduction_id);
CREATE INDEX company_deduction_company_id ON company_deduction(company_id);
CREATE INDEX recurring_holiday_company_id ON recurring_holiday(company_id);
CREATE INDEX accrual_balance_user_id ON accrual_balance(user_id);
CREATE INDEX permission_user_permission_control_id ON permission_user(permission_control_id);
CREATE INDEX permission_user_user_id ON permission_user(user_id);

delete from user_preference where id in ( select * from ( select a.id from ( select user_id from user_preference group by user_id having count(*) > 1 ) as tmp, user_preference as a WHERE a.user_id = tmp.user_id ) as tmp );
CREATE UNIQUE INDEX user_preference_user_id_ukey ON user_preference(user_id);
