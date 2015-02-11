alter table accrual_policy add column apply_frequency_id smallint;
alter table accrual_policy add column apply_frequency_month smallint;
alter table accrual_policy add column apply_frequency_day_of_month smallint;
alter table accrual_policy add column apply_frequency_day_of_week smallint;
alter table accrual_policy add column milestone_rollover_hire_date smallint;
alter table accrual_policy add column milestone_rollover_month smallint;
alter table accrual_policy add column milestone_rollover_day_of_month smallint;
alter table accrual_policy add column minimum_employed_days integer;
alter table accrual_policy add column minimum_employed_days_catchup smallint;

alter table accrual_policy change maximum maximum_time integer;
alter table accrual_policy change minimum minimum_time integer;

CREATE TABLE accrual_policy_milestone (
	id serial NOT NULL,
	accrual_policy_id integer NOT NULL,
	length_of_service numeric(9,2),
	length_of_service_unit_id smallint,
	length_of_service_days numeric(9,2), 
	accrual_rate integer, 
	minimum_time integer,
	maximum_time integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

alter table policy_group add column accrual_policy_id integer;

alter table user_wage add column labor_burden_percent numeric(9,2);
alter table user_wage add column note text;
