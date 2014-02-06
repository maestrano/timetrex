alter table user_wage alter column wage type numeric(20,4);
alter table user_wage alter column hourly_rate type numeric(20,4);

alter table pay_stub_entry alter column rate type numeric(20,4);
alter table pay_stub_entry alter column units type numeric(20,4);
alter table pay_stub_entry alter column ytd_units type numeric(20,4);
alter table pay_stub_entry alter column amount type numeric(20,4);
alter table pay_stub_entry alter column ytd_amount type numeric(20,4);

alter table pay_stub_amendment alter column rate type numeric(20,4);
alter table pay_stub_amendment alter column units type numeric(20,4);
alter table pay_stub_amendment alter column amount type numeric(20,4);
alter table pay_stub_amendment alter column percent_amount type numeric(20,4);

alter table recurring_ps_amendment alter column rate type numeric(20,4);
alter table recurring_ps_amendment alter column units type numeric(20,4);
alter table recurring_ps_amendment alter column amount type numeric(20,4);
alter table recurring_ps_amendment alter column percent_amount type numeric(20,4);

alter table company_deduction add column minimum_user_age numeric(11,4);
alter table company_deduction add column maximum_user_age numeric(11,4);

alter table holiday_policy add column average_days integer;
update holiday_policy set average_days = average_time_days;
