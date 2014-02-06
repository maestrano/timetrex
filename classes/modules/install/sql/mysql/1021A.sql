alter table accrual_policy_milestone change type accrual_rate decimal(18,4);
alter table accrual change type amount decimal(18,4);
alter table accrual_balance change type balance decimal(18,4);

