alter table user_wage change wage wage numeric(20,4); 
alter table user_wage change hourly_rate hourly_rate numeric(20,4);

alter table pay_stub_entry change rate rate numeric(20,4);
alter table pay_stub_entry change units units numeric(20,4);
alter table pay_stub_entry change ytd_units ytd_units numeric(20,4);
alter table pay_stub_entry change amount amount numeric(20,4);
alter table pay_stub_entry change ytd_amount ytd_amount numeric(20,4);

alter table pay_stub_amendment change rate rate numeric(20,4);
alter table pay_stub_amendment change units units numeric(20,4);
alter table pay_stub_amendment change amount amount numeric(20,4);
alter table pay_stub_amendment change percent_amount percent_amount numeric(20,4);

alter table recurring_ps_amendment change rate rate numeric(20,4);
alter table recurring_ps_amendment change units units numeric(20,4);
alter table recurring_ps_amendment change amount amount numeric(20,4);
alter table recurring_ps_amendment change percent_amount percent_amount numeric(20,4);

alter table company_deduction add column minimum_user_age numeric(11,4);
alter table company_deduction add column maximum_user_age numeric(11,4);

alter table holiday_policy add column average_days integer;
update holiday_policy set average_days = average_time_days;
