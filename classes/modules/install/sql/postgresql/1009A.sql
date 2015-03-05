alter table "accrual_policy" add column apply_frequency_id smallint;
alter table "accrual_policy" add column apply_frequency_month smallint;
alter table "accrual_policy" add column apply_frequency_day_of_month smallint;
alter table "accrual_policy" add column apply_frequency_day_of_week smallint;
alter table "accrual_policy" add column milestone_rollover_hire_date smallint;
alter table "accrual_policy" add column milestone_rollover_month smallint;
alter table "accrual_policy" add column milestone_rollover_day_of_month smallint;
alter table "accrual_policy" add column minimum_employed_days integer;
alter table "accrual_policy" add column minimum_employed_days_catchup smallint;
alter table "accrual_policy" rename column maximum to maximum_time;
alter table "accrual_policy" rename column minimum to minimum_time;

CREATE TABLE accrual_policy_milestone (
	id serial NOT NULL,
	accrual_policy_id integer NOT NULL,
	length_of_service numeric, --set to numeric(9,2) in MYSQL
	length_of_service_unit_id smallint, --ie: days, weeks,months,years
	length_of_service_days numeric, --set to numeric(9,2) in MYSQL
	accrual_rate integer, --seconds
	minimum_time integer,
	maximum_time integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);

alter table "policy_group" add column accrual_policy_id integer;

alter table "user_group" alter column deleted drop default;
alter table "user_group" alter column deleted type smallint USING CASE WHEN deleted = 't' THEN 1 ELSE 0 END;
alter table "user_group" alter column deleted set default 0;

alter table "user_wage" add column labor_burden_percent numeric;
alter table "user_wage" add column note text;
