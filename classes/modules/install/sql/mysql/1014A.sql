CREATE TABLE permission_control (
    id serial NOT NULL,
	company_id integer NOT NULL,
    name varchar(250) NOT NULL,
	description varchar(250) NOT NULL,
    created_date integer,
    created_by integer,
    updated_date integer,
    updated_by integer,
    deleted_date integer,
    deleted_by integer,
    deleted smallint DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) Engine=InnoDB;

CREATE TABLE permission_user (
    id serial NOT NULL,
	permission_control_id integer NOT NULL,
	user_id integer NOT NULL,
	PRIMARY KEY(id)
) Engine=InnoDB;

alter table permission rename to permission_old;

CREATE TABLE permission (
    id serial NOT NULL,
    permission_control_id integer NOT NULL,
    section varchar(250),
    name varchar(250),
    value varchar(250),
    created_date integer,
    created_by integer,
    updated_date integer,
    updated_by integer,
    deleted_date integer,
    deleted_by integer,
    deleted smallint DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) Engine=InnoDB;
CREATE INDEX permission_permission_control_id ON permission(permission_control_id);

alter table user_default add column permission_control_id integer;

alter table meal_policy add column include_lunch_punch_time smallint;
update meal_policy set include_lunch_punch_time = 0;

alter table user_date_total add column meal_policy_id integer;
update user_date_total set meal_policy_id = 0;

alter table premium_policy add column daily_trigger_time integer;
alter table premium_policy add column weekly_trigger_time integer;
alter table premium_policy add column minimum_time integer;
alter table premium_policy add column maximum_time integer;
alter table premium_policy add column include_meal_policy smallint;
alter table premium_policy add column exclude_default_branch smallint;
alter table premium_policy add column exclude_default_department smallint;
alter table premium_policy add column branch_selection_type_id smallint;
alter table premium_policy add column department_selection_type_id smallint;

alter table premium_policy add column job_selection_type_id smallint;
alter table premium_policy add column job_group_selection_type_id smallint;
alter table premium_policy add column job_item_selection_type_id smallint;
alter table premium_policy add column job_item_group_selection_type_id smallint;

update premium_policy set daily_trigger_time = 0;
update premium_policy set weekly_trigger_time = 0;
update premium_policy set minimum_time = 0;
update premium_policy set maximum_time = 0;

update premium_policy set exclude_default_branch = 0;
update premium_policy set exclude_default_department = 0;
update premium_policy set branch_selection_type_id = 10;
update premium_policy set department_selection_type_id = 10;
update premium_policy set job_group_selection_type_id = 10;
update premium_policy set job_selection_type_id = 10;
update premium_policy set job_item_group_selection_type_id = 10;
update premium_policy set job_item_selection_type_id = 10;

CREATE TABLE premium_policy_branch (
    id serial NOT NULL,
	premium_policy_id integer NOT NULL,
	branch_id integer NOT NULL,
	PRIMARY KEY(id)
) Engine=InnoDB;

CREATE TABLE premium_policy_department (
    id serial NOT NULL,
	premium_policy_id integer NOT NULL,
	department_id integer NOT NULL,
	PRIMARY KEY(id)
) Engine=InnoDB;

alter table premium_policy change rate rate decimal(9,4);
alter table premium_policy change accrual_rate accrual_rate numeric(9,4);
alter table over_time_policy change rate rate numeric(9,4);
alter table over_time_policy change accrual_rate accrual_rate numeric(9,4);

ALTER TABLE absence_policy ENGINE=InnoDB;
ALTER TABLE accrual ENGINE=InnoDB;
ALTER TABLE accrual_balance ENGINE=InnoDB;
ALTER TABLE accrual_policy ENGINE=InnoDB;
ALTER TABLE authentication ENGINE=InnoDB;
ALTER TABLE authorizations ENGINE=InnoDB;
ALTER TABLE bank_account ENGINE=InnoDB;
ALTER TABLE branch ENGINE=InnoDB;
ALTER TABLE bread_crumb ENGINE=InnoDB;
ALTER TABLE company ENGINE=InnoDB;
ALTER TABLE company_deduction ENGINE=InnoDB;
ALTER TABLE company_deduction_pay_stub_entry_account ENGINE=InnoDB;
ALTER TABLE cron ENGINE=InnoDB;
ALTER TABLE department ENGINE=InnoDB;
ALTER TABLE department_branch ENGINE=InnoDB;
ALTER TABLE department_branch_user ENGINE=InnoDB;
ALTER TABLE exception ENGINE=InnoDB;
ALTER TABLE exception_policy ENGINE=InnoDB;
ALTER TABLE exception_policy_control ENGINE=InnoDB;
ALTER TABLE help ENGINE=InnoDB;
ALTER TABLE help_group ENGINE=InnoDB;
ALTER TABLE help_group_control ENGINE=InnoDB;
ALTER TABLE hierarchy_control ENGINE=InnoDB;
ALTER TABLE hierarchy_object_type ENGINE=InnoDB;
ALTER TABLE hierarchy_share ENGINE=InnoDB;
ALTER TABLE hierarchy_tree ENGINE=InnoDB;
ALTER TABLE holiday_policy ENGINE=InnoDB;
ALTER TABLE holiday_policy_recurring_holiday ENGINE=InnoDB;
ALTER TABLE holidays ENGINE=InnoDB;
ALTER TABLE system_log ENGINE=InnoDB;
ALTER TABLE meal_policy ENGINE=InnoDB;
ALTER TABLE message ENGINE=InnoDB;
ALTER TABLE other_field ENGINE=InnoDB;
ALTER TABLE over_time_policy ENGINE=InnoDB;
ALTER TABLE pay_period ENGINE=InnoDB;
ALTER TABLE pay_period_schedule ENGINE=InnoDB;
ALTER TABLE pay_period_schedule_user ENGINE=InnoDB;
ALTER TABLE pay_stub ENGINE=InnoDB;
ALTER TABLE pay_stub_amendment ENGINE=InnoDB;
ALTER TABLE pay_stub_entry ENGINE=InnoDB;
ALTER TABLE pay_stub_entry_account ENGINE=InnoDB;
ALTER TABLE pay_stub_entry_account_link ENGINE=InnoDB;
ALTER TABLE permission ENGINE=InnoDB;
ALTER TABLE policy_group ENGINE=InnoDB;
ALTER TABLE policy_group_over_time_policy ENGINE=InnoDB;
ALTER TABLE policy_group_premium_policy ENGINE=InnoDB;
ALTER TABLE policy_group_round_interval_policy ENGINE=InnoDB;
ALTER TABLE policy_group_user ENGINE=InnoDB;
ALTER TABLE premium_policy ENGINE=InnoDB;
ALTER TABLE punch ENGINE=InnoDB;
ALTER TABLE punch_control ENGINE=InnoDB;
ALTER TABLE recurring_holiday ENGINE=InnoDB;
ALTER TABLE recurring_ps_amendment ENGINE=InnoDB;
ALTER TABLE recurring_ps_amendment_user ENGINE=InnoDB;
ALTER TABLE recurring_schedule_control ENGINE=InnoDB;
ALTER TABLE recurring_schedule_template ENGINE=InnoDB;
ALTER TABLE recurring_schedule_template_control ENGINE=InnoDB;
ALTER TABLE recurring_schedule_user ENGINE=InnoDB;
ALTER TABLE request ENGINE=InnoDB;
ALTER TABLE roe ENGINE=InnoDB;
ALTER TABLE round_interval_policy ENGINE=InnoDB;
ALTER TABLE schedule ENGINE=InnoDB;
ALTER TABLE schedule_policy ENGINE=InnoDB;
ALTER TABLE station ENGINE=InnoDB;
ALTER TABLE station_user ENGINE=InnoDB;
ALTER TABLE system_setting ENGINE=InnoDB;
ALTER TABLE users ENGINE=InnoDB;
ALTER TABLE user_date ENGINE=InnoDB;
ALTER TABLE user_date_total ENGINE=InnoDB;
ALTER TABLE user_deduction ENGINE=InnoDB;
ALTER TABLE user_default ENGINE=InnoDB;
ALTER TABLE user_default_company_deduction ENGINE=InnoDB;
ALTER TABLE user_generic_data ENGINE=InnoDB;
ALTER TABLE user_pay_period_total ENGINE=InnoDB;
ALTER TABLE user_preference ENGINE=InnoDB;
ALTER TABLE user_tax ENGINE=InnoDB;
ALTER TABLE user_title ENGINE=InnoDB;
ALTER TABLE user_wage ENGINE=InnoDB;
ALTER TABLE pay_period_time_sheet_verify ENGINE=InnoDB;
ALTER TABLE company_user_count ENGINE=InnoDB;
ALTER TABLE user_generic_status ENGINE=InnoDB;
ALTER TABLE user_group ENGINE=InnoDB;
ALTER TABLE user_group_tree ENGINE=InnoDB;
ALTER TABLE accrual_policy_milestone ENGINE=InnoDB;
ALTER TABLE currency ENGINE=InnoDB;
ALTER TABLE policy_group_accrual_policy ENGINE=InnoDB;

ALTER TABLE income_tax_rate ENGINE=InnoDB;
ALTER TABLE income_tax_rate_us ENGINE=InnoDB;