alter table pay_period_schedule add column start_day_of_week smallint;
alter table pay_period_schedule add column transaction_date smallint;

alter table pay_period_schedule add column primary_day_of_month smallint;
alter table pay_period_schedule add column secondary_day_of_month smallint;
alter table pay_period_schedule add column primary_transaction_day_of_month smallint;
alter table pay_period_schedule add column secondary_transaction_day_of_month smallint;
alter table pay_period_schedule add column transaction_date_bd smallint;