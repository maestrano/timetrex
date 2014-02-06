alter table pay_period_schedule add column new_day_trigger_time integer;
alter table pay_period_schedule add column maximum_shift_time integer;
update pay_period_schedule set new_day_trigger_time = 14400;
update pay_period_schedule set maximum_shift_time = 57600;
