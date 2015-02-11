CREATE TABLE pay_code (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	description character varying NULL,
	code character varying NOT NULL, --Used for exporting to payroll software?
	type_id smallint NOT NULL,
	pay_formula_policy_id integer DEFAULT 0,
	pay_stub_entry_account_id integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);

CREATE TABLE pay_formula_policy (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	description character varying NULL,
	wage_source_type_id smallint DEFAULT 10,
	wage_source_contributing_shift_policy_id integer NOT NULL DEFAULT 0,
	time_source_contributing_shift_policy_id integer NOT NULL DEFAULT 0,
	wage_group_id integer,
	pay_type_id smallint DEFAULT 10,
	rate numeric(9,4),
	custom_formula character varying NULL,
	accrual_policy_account_id integer,
	accrual_rate numeric(9,4),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);

CREATE TABLE regular_time_policy (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	description character varying NULL,
	contributing_shift_policy_id integer NOT NULL,
	calculation_order integer,
	pay_formula_policy_id integer DEFAULT 0,
	pay_code_id integer,
	branch_selection_type_id smallint DEFAULT 10,
	exclude_default_branch smallint DEFAULT 0,
	department_selection_type_id smallint DEFAULT 10,
	exclude_default_department smallint DEFAULT 0,
	job_group_selection_type_id smallint DEFAULT 10,
	job_selection_type_id smallint DEFAULT 10,
	exclude_default_job smallint DEFAULT 0,
	job_item_group_selection_type_id smallint DEFAULT 10,
	job_item_selection_type_id smallint DEFAULT 10,
	exclude_default_job_item smallint DEFAULT 0,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);

-- Use company generic map to select regular,OT,prem,absence,meal,break policies by type;
CREATE TABLE contributing_pay_code_policy (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	description character varying NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);

-- Use company generic map to select branch,department,job,task;
-- Use company generic map to select holiday policies;
CREATE TABLE contributing_shift_policy (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	description character varying NULL,
	contributing_pay_code_policy_id integer,

	filter_start_date date,
	filter_end_date date,
	filter_start_time time,
	filter_end_time time,
	filter_minimum_time integer,
	filter_maximum_time integer,
	include_partial_shift smallint DEFAULT 0,

	branch_selection_type_id smallint DEFAULT 10,
	exclude_default_branch smallint DEFAULT 0,
	department_selection_type_id smallint DEFAULT 10,
	exclude_default_department smallint DEFAULT 0,
	job_group_selection_type_id smallint DEFAULT 10,
	job_selection_type_id smallint DEFAULT 10,
	exclude_default_job smallint DEFAULT 0,
	job_item_group_selection_type_id smallint DEFAULT 10,
	job_item_selection_type_id smallint DEFAULT 10,
	exclude_default_job_item smallint DEFAULT 0,

	sun smallint DEFAULT 1,
	mon smallint DEFAULT 1,
	tue smallint DEFAULT 1,
	wed smallint DEFAULT 1,
	thu smallint DEFAULT 1,
	fri smallint DEFAULT 1,
	sat smallint DEFAULT 1,

	include_schedule_shift_type_id integer DEFAULT 0,
	include_holiday_type_id integer DEFAULT 10,

	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);

ALTER TABLE absence_policy ALTER COLUMN type_id SET DEFAULT 0;

ALTER TABLE meal_policy ADD COLUMN pay_code_id integer DEFAULT 0;
ALTER TABLE break_policy ADD COLUMN pay_code_id integer DEFAULT 0;
ALTER TABLE over_time_policy ADD COLUMN pay_code_id integer DEFAULT 0;
ALTER TABLE premium_policy ADD COLUMN pay_code_id integer DEFAULT 0;
ALTER TABLE absence_policy ADD COLUMN pay_code_id integer DEFAULT 0;

ALTER TABLE meal_policy ADD COLUMN pay_formula_policy_id integer DEFAULT 0;
ALTER TABLE break_policy ADD COLUMN pay_formula_policy_id integer DEFAULT 0;
ALTER TABLE over_time_policy ADD COLUMN pay_formula_policy_id integer DEFAULT 0;
ALTER TABLE premium_policy ADD COLUMN pay_formula_policy_id integer DEFAULT 0;
ALTER TABLE absence_policy ADD COLUMN pay_formula_policy_id integer DEFAULT 0;

ALTER TABLE over_time_policy ADD COLUMN contributing_shift_policy_id integer DEFAULT 0;
ALTER TABLE premium_policy ADD COLUMN contributing_shift_policy_id integer DEFAULT 0;
ALTER TABLE holiday_policy ADD COLUMN contributing_shift_policy_id integer DEFAULT 0;
ALTER TABLE holiday_policy ADD COLUMN eligible_contributing_shift_policy_id integer DEFAULT 0;

ALTER TABLE premium_policy ADD COLUMN exclude_default_job smallint DEFAULT 0;
ALTER TABLE premium_policy ADD COLUMN exclude_default_job_item smallint DEFAULT 0;

-- Contributing pay codes is faster to calculated for long periods of time;
ALTER TABLE accrual_policy ADD COLUMN contributing_shift_policy_id integer DEFAULT 0;
ALTER TABLE accrual_policy ADD COLUMN length_of_service_contributing_pay_code_policy_id integer DEFAULT 0;
ALTER TABLE company_deduction ADD COLUMN length_of_service_contributing_pay_code_policy_id integer DEFAULT 0;

-- Add differential criteria to overtime policies;
ALTER TABLE over_time_policy ADD COLUMN branch_selection_type_id smallint DEFAULT 10;
ALTER TABLE over_time_policy ADD COLUMN exclude_default_branch smallint DEFAULT 0;
ALTER TABLE over_time_policy ADD COLUMN department_selection_type_id smallint DEFAULT 10;
ALTER TABLE over_time_policy ADD COLUMN exclude_default_department smallint DEFAULT 0;
ALTER TABLE over_time_policy ADD COLUMN job_group_selection_type_id smallint DEFAULT 10;
ALTER TABLE over_time_policy ADD COLUMN job_selection_type_id smallint DEFAULT 10;
ALTER TABLE over_time_policy ADD COLUMN exclude_default_job smallint DEFAULT 0;
ALTER TABLE over_time_policy ADD COLUMN job_item_group_selection_type_id smallint DEFAULT 10;
ALTER TABLE over_time_policy ADD COLUMN job_item_selection_type_id smallint DEFAULT 10;
ALTER TABLE over_time_policy ADD COLUMN exclude_default_job_item smallint DEFAULT 0;

-- Allow user to define what branch,department,job,task are used for auto-add/auto-deduct meals/breaks. Allow for user defaults, or punch defaults;
ALTER TABLE meal_policy ADD COLUMN branch_id integer DEFAULT 0;
ALTER TABLE meal_policy ADD COLUMN department_id integer DEFAULT 0;
ALTER TABLE meal_policy ADD COLUMN job_id integer DEFAULT 0;
ALTER TABLE meal_policy ADD COLUMN job_item_id integer DEFAULT 0;

ALTER TABLE break_policy ADD COLUMN branch_id integer DEFAULT 0;
ALTER TABLE break_policy ADD COLUMN department_id integer DEFAULT 0;
ALTER TABLE break_policy ADD COLUMN job_id integer DEFAULT 0;
ALTER TABLE break_policy ADD COLUMN job_item_id integer DEFAULT 0;

ALTER TABLE meal_policy ADD COLUMN description character varying;
ALTER TABLE break_policy ADD COLUMN description character varying;
ALTER TABLE holiday_policy ADD COLUMN description character varying;
ALTER TABLE exception_policy_control ADD COLUMN description character varying;
ALTER TABLE schedule_policy ADD COLUMN description character varying;
ALTER TABLE over_time_policy ADD COLUMN description character varying;
ALTER TABLE premium_policy ADD COLUMN description character varying;
ALTER TABLE absence_policy ADD COLUMN description character varying;
ALTER TABLE accrual_policy ADD COLUMN description character varying;
ALTER TABLE round_interval_policy ADD COLUMN description character varying;
ALTER TABLE policy_group ADD COLUMN description character varying;

ALTER TABLE accrual ADD COLUMN note character varying;

CREATE TABLE accrual_policy_account (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	description character varying,
	enable_pay_stub_balance_display smallint DEFAULT 0,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);
ALTER TABLE accrual_policy ADD COLUMN accrual_policy_account_id integer;

--Switch Accrual entries associated with hour-based accrual policies to type_id = 76 rather than type_id=75;
UPDATE accrual set type_id = 76 WHERE id in ( SELECT a.id from accrual as a LEFT JOIN accrual_policy as b ON ( a.accrual_policy_id = b.id ) WHERE a.type_id = 75 AND b.type_id = 30 );

ALTER TABLE accrual RENAME COLUMN accrual_policy_id to accrual_policy_account_id;
ALTER TABLE accrual ADD COLUMN accrual_policy_id integer DEFAULT 0;
UPDATE accrual SET accrual_policy_id = accrual_policy_account_id;
ALTER TABLE accrual_balance RENAME COLUMN accrual_policy_id to accrual_policy_account_id;

CREATE INDEX "pay_stub_entry_name_id" ON pay_stub_entry(pay_stub_entry_name_id);