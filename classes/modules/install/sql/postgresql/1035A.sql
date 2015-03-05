ALTER TABLE accrual_policy_milestone ADD COLUMN rollover_time integer;
update accrual_policy_milestone set rollover_time = maximum_time;

