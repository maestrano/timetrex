CREATE TABLE wage_group (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name varchar(250),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);
CREATE INDEX wage_group_id ON wage_group(id);

ALTER TABLE user_wage ADD COLUMN wage_group_id integer DEFAULT 0 NOT NULL;
ALTER TABLE user_wage ADD COLUMN hourly_rate numeric(11,4) NULL;
ALTER TABLE user_wage CHANGE wage wage numeric(11,4);

ALTER TABLE over_time_policy ADD COLUMN wage_group_id integer DEFAULT 0 NOT NULL;

ALTER TABLE absence_policy ADD COLUMN wage_group_id integer DEFAULT 0 NOT NULL;
ALTER TABLE absence_policy ADD COLUMN rate numeric(9,4) NULL;
ALTER TABLE absence_policy ADD COLUMN accrual_rate numeric(9,4) NULL;
UPDATE absence_policy set rate = 1.0;
UPDATE absence_policy set accrual_rate = 1.0;

ALTER TABLE premium_policy ADD COLUMN wage_group_id integer DEFAULT 0 NOT NULL;

ALTER TABLE pay_period_schedule ADD COLUMN shift_assigned_day_id integer NULL;
ALTER TABLE pay_period_schedule ADD COLUMN timesheet_verify_before_end_date integer NULL;
ALTER TABLE pay_period_schedule ADD COLUMN timesheet_verify_before_transaction_date integer NULL;
ALTER TABLE pay_period_schedule ADD COLUMN timesheet_verify_notice_before_transaction_date integer NULL;
ALTER TABLE pay_period_schedule ADD COLUMN timesheet_verify_notice_email integer NULL;
ALTER TABLE pay_period_schedule ADD COLUMN annual_pay_periods integer NULL;


ALTER TABLE meal_policy ADD COLUMN auto_detect_type_id integer DEFAULT 10 NOT NULL;
ALTER TABLE meal_policy ADD COLUMN minimum_punch_time integer;
ALTER TABLE meal_policy ADD COLUMN maximum_punch_time integer;

CREATE TABLE company_generic_map (
	id serial NOT NULL,
	company_id integer NOT NULL,
	object_type_id integer NOT NULL,
	object_id integer NULL,
	map_id integer NULL
);
CREATE INDEX company_generic_map_id ON company_generic_map(id);
CREATE INDEX company_generic_map_company_id_object_type_id_object_id ON company_generic_map(company_id,object_type_id,object_id);

insert into company_generic_map (company_id,object_type_id,object_id,map_id) (select a.company_id as company_id,110 as object_type_id,a.id as object_id,b.over_time_policy_id as map_id from policy_group as a, policy_group_over_time_policy as b WHERE a.id = b.policy_group_id);
insert into company_generic_map (company_id,object_type_id,object_id,map_id) (select a.company_id as company_id,120 as object_type_id,a.id as object_id,b.premium_policy_id as map_id from policy_group as a, policy_group_premium_policy as b WHERE a.id = b.policy_group_id);
insert into company_generic_map (company_id,object_type_id,object_id,map_id) (select a.company_id as company_id,130 as object_type_id,a.id as object_id,b.round_interval_policy_id as map_id from policy_group as a, policy_group_round_interval_policy as b WHERE a.id = b.policy_group_id);
insert into company_generic_map (company_id,object_type_id,object_id,map_id) (select a.company_id as company_id,140 as object_type_id,a.id as object_id,b.accrual_policy_id as map_id from policy_group as a, policy_group_accrual_policy as b WHERE a.id = b.policy_group_id);
insert into company_generic_map (company_id,object_type_id,object_id,map_id) (select a.company_id as company_id,150 as object_type_id,a.id as object_id,a.meal_policy_id as map_id from policy_group as a WHERE a.meal_policy_id is not NULL);
insert into company_generic_map (company_id,object_type_id,object_id,map_id) (select a.company_id as company_id,180 as object_type_id,a.id as object_id,a.holiday_policy_id as map_id from policy_group as a WHERE a.holiday_policy_id is not NULL);
UPDATE company_generic_map_id_seq set ID = ( select max(id) from company_generic_map )+1;

ALTER TABLE policy_group DROP column meal_policy_id;
ALTER TABLE policy_group DROP column holiday_policy_id;

drop table policy_group_accrual_policy;
drop table policy_group_round_interval_policy;
drop table policy_group_premium_policy;
drop table policy_group_over_time_policy;

CREATE TABLE break_policy (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name varchar(250) NOT NULL,
	type_id integer NOT NULL,
	trigger_time integer,
	amount integer NOT NULL,
	auto_detect_type_id integer NOT NULL,
	start_window integer,
	window_length integer,
	minimum_punch_time integer,
	maximum_punch_time integer,
	include_break_punch_time smallint,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);

ALTER TABLE user_date_total ADD COLUMN break_policy_id integer DEFAULT 0;
ALTER TABLE premium_policy ADD COLUMN include_break_policy smallint DEFAULT 0;

update exception_policy set type_id = 'S1' where type_id = 'A';
update exception_policy set type_id = 'S2' where type_id = 'B';
update exception_policy set type_id = 'S3' where type_id = 'C';
update exception_policy set type_id = 'S4' where type_id = 'D';
update exception_policy set type_id = 'S5' where type_id = 'E';
update exception_policy set type_id = 'S6' where type_id = 'F';
update exception_policy set type_id = 'S7' where type_id = 'G';
update exception_policy set type_id = 'S8' where type_id = 'H';
update exception_policy set type_id = 'M1' where type_id = 'K';
update exception_policy set type_id = 'M2' where type_id = 'L';
update exception_policy set type_id = 'L1' where type_id = 'M';
update exception_policy set type_id = 'L2' where type_id = 'N';
update exception_policy set type_id = 'L3' where type_id = 'O';
update exception_policy set type_id = 'M3' where type_id = 'P';
update exception_policy set type_id = 'J1' where type_id = 'T';
update exception_policy set type_id = 'J2' where type_id = 'U';
update exception_policy set type_id = 'J3' where type_id = 'V';
update exception_policy set type_id = 'J4' where type_id = 'W';

update holiday_policy set type_id = 30 where type_id = 10;
ALTER TABLE holiday_policy ADD COLUMN worked_scheduled_days smallint DEFAULT 0;
ALTER TABLE holiday_policy ADD COLUMN minimum_worked_after_period_days integer DEFAULT 0;
ALTER TABLE holiday_policy ADD COLUMN minimum_worked_after_days integer DEFAULT 0;
ALTER TABLE holiday_policy ADD COLUMN worked_after_scheduled_days smallint DEFAULT 0;
ALTER TABLE holiday_policy ADD COLUMN paid_absence_as_worked  smallint DEFAULT 0;

CREATE TABLE hierarchy_level (
	id serial NOT NULL,
	hierarchy_control_id integer NOT NULL,
	level integer NOT NULL,
	user_id integer NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted integer DEFAULT 0 NOT NULL
);
CREATE INDEX hierarchy_level_hierarchy_control_id_user_id ON hierarchy_level(hierarchy_control_id,user_id);

CREATE TABLE hierarchy_user (
	id serial NOT NULL,
	hierarchy_control_id integer NOT NULL,
	user_id integer NOT NULL
);
CREATE INDEX hierarchy_user_hierarchy_control_id_user_id ON hierarchy_user(hierarchy_control_id,user_id);

ALTER TABLE request ADD COLUMN authorization_level smallint DEFAULT 99;
ALTER TABLE pay_period_time_sheet_verify ADD COLUMN authorization_level smallint DEFAULT 99;

create index recurring_schedule_template_schedule_template_control_id ON recurring_schedule_template(recurring_schedule_template_control_id);

ALTER TABLE pay_stub_entry CHANGE rate rate numeric(11,4);
ALTER TABLE pay_stub_entry CHANGE units units numeric(11,4);
ALTER TABLE pay_stub_entry CHANGE ytd_units ytd_units numeric(11,4);

ALTER TABLE pay_stub_amendment CHANGE rate rate numeric(11,4);
ALTER TABLE pay_stub_amendment CHANGE units units numeric(11,4);
ALTER TABLE recurring_ps_amendment CHANGE rate rate numeric(11,4);
ALTER TABLE recurring_ps_amendment CHANGE units units numeric(11,4);

ALTER TABLE recurring_holiday CHANGE easter special_day smallint(1);

UPDATE branch set province = 'NL' where province = 'NF';
UPDATE company set province = 'NL' where province = 'NF';
UPDATE users set province = 'NL' where province = 'NF';
UPDATE user_default set province = 'NL' where province = 'NF';
UPDATE branch set province = 'YU' where province = 'YT';
UPDATE company set province = 'YU' where province = 'YT';
UPDATE users set province = 'YU' where province = 'YT';
UPDATE user_default set province = 'YU' where province = 'YT';

ALTER TABLE company_deduction ADD COLUMN start_date timestamp;
ALTER TABLE company_deduction ADD COLUMN end_date timestamp;
ALTER TABLE company_deduction ADD COLUMN minimum_length_of_service numeric(11,4);
ALTER TABLE company_deduction ADD COLUMN minimum_length_of_service_unit_id smallint;
ALTER TABLE company_deduction ADD COLUMN minimum_length_of_service_days numeric(11,4);
ALTER TABLE company_deduction ADD COLUMN maximum_length_of_service numeric(11,4);
ALTER TABLE company_deduction ADD COLUMN maximum_length_of_service_unit_id smallint;
ALTER TABLE company_deduction ADD COLUMN maximum_length_of_service_days numeric(11,4);
ALTER TABLE company_deduction ADD COLUMN include_account_amount_type_id smallint DEFAULT 10;
ALTER TABLE company_deduction ADD COLUMN exclude_account_amount_type_id smallint DEFAULT 10;

ALTER TABLE station ADD COLUMN job_id integer DEFAULT 0;
ALTER TABLE station ADD COLUMN job_item_id integer DEFAULT 0;
