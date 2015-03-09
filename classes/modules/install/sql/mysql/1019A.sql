ALTER TABLE users ADD COLUMN second_last_name varchar(250);
ALTER TABLE company ADD COLUMN enable_second_last_name boolean DEFAULT false NOT NULL;
ALTER TABLE accrual_policy ADD COLUMN enable_pay_stub_balance_display boolean DEFAULT false NOT NULL;

alter table accrual_policy_milestone change accrual_rate type numeric(18,4);
alter table accrual change amount type numeric(18,4);
alter table accrual_balance change balance type numeric(18,4);

alter table exception add column authorized boolean DEFAULT false NOT NULL;
update exception set type_id = 50 where type_id = 10;

alter table exception_policy add column enable_authorization boolean DEFAULT false NOT NULL;
alter table exception_policy add column email_notification_id integer DEFAULT false NOT NULL;

alter table user_preference add column enable_email_notification_exception boolean DEFAULT false NOT NULL;
alter table user_preference add column enable_email_notification_message boolean DEFAULT false NOT NULL;
alter table user_preference add column enable_email_notification_home boolean DEFAULT false NOT NULL;

alter table user_default add column enable_email_notification_exception boolean DEFAULT false NOT NULL;
alter table user_default add column enable_email_notification_message boolean DEFAULT false NOT NULL;
alter table user_default add column enable_email_notification_home boolean DEFAULT false NOT NULL;

update premium_policy set type_id = 100 where type_id = 10;
alter table premium_policy add column maximum_no_break_time integer DEFAULT NULL;
alter table premium_policy add column minimum_break_time integer DEFAULT NULL;
