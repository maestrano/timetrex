alter table accrual_policy_milestone change accrual_rate accrual_rate decimal(18,4);
alter table accrual change amount amount decimal(18,4);
alter table accrual_balance change balance balance decimal(18,4);

alter table station add enable_auto_punch_status boolean DEFAULT 0 NOT NULL;
alter table station add mode_flag bigint DEFAULT 1 NOT NULL;
