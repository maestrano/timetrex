ALTER TABLE users ADD COLUMN second_last_name character varying;
ALTER TABLE company ADD COLUMN enable_second_last_name smallint NOT NULL DEFAULT 0;
ALTER TABLE accrual_policy ADD COLUMN enable_pay_stub_balance_display smallint NOT NULL DEFAULT 0;

alter table accrual_policy_milestone alter column accrual_rate type numeric;
alter table accrual alter column amount type numeric;
alter table accrual_balance alter column balance type numeric;

alter table exception add column authorized smallint NOT NULL DEFAULT 0;
update exception set type_id = 50 where type_id = 10;

alter table exception_policy add column enable_authorization smallint NOT NULL DEFAULT 0;
alter table exception_policy add column email_notification_id integer NOT NULL DEFAULT 0;

alter table user_preference add column enable_email_notification_exception smallint NOT NULL DEFAULT 1;
alter table user_preference add column enable_email_notification_message smallint NOT NULL DEFAULT 1;
alter table user_preference add column enable_email_notification_home smallint NOT NULL DEFAULT 0;

alter table user_default add column enable_email_notification_exception smallint NOT NULL DEFAULT 1;
alter table user_default add column enable_email_notification_message smallint NOT NULL DEFAULT 1;
alter table user_default add column enable_email_notification_home smallint NOT NULL DEFAULT 0;

update premium_policy set type_id = 100 where type_id = 10;
alter table premium_policy add column maximum_no_break_time integer DEFAULT NULL;
alter table premium_policy add column minimum_break_time integer DEFAULT NULL;