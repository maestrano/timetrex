alter table accrual_policy_milestone change type accrual_rate numeric(18,4);
alter table accrual change type amount numeric(18,4);
alter table accrual_balance change type balance numeric(18,4);

