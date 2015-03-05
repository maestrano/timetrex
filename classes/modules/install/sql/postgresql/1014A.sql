CREATE TABLE permission_control (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	description character varying NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);

CREATE TABLE permission_user (
	id serial NOT NULL,
	permission_control_id integer NOT NULL,
	user_id integer NOT NULL
);

--Rename current permission table;
alter table "permission" rename to "permission_old";

--Create totally new permission table;
CREATE TABLE permission (
	id serial NOT NULL,
	permission_control_id integer NOT NULL,
	section character varying,
	name character varying,
	value character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);
CREATE INDEX permission_permission_control_id ON permission USING btree (permission_control_id);

alter table "user_default" add column permission_control_id integer;

alter table "meal_policy" add column include_lunch_punch_time smallint;
update meal_policy set include_lunch_punch_time = 0;

alter table "user_date_total" add column meal_policy_id integer;
update user_date_total set meal_policy_id = 0;

alter table "premium_policy" add column daily_trigger_time integer;
alter table "premium_policy" add column weekly_trigger_time integer;
alter table "premium_policy" add column minimum_time integer;
alter table "premium_policy" add column maximum_time integer;
alter table "premium_policy" add column include_meal_policy smallint;
alter table "premium_policy" add column exclude_default_branch smallint;
alter table "premium_policy" add column exclude_default_department smallint;
alter table "premium_policy" add column branch_selection_type_id smallint;
alter table "premium_policy" add column department_selection_type_id smallint;

alter table "premium_policy" add column job_group_selection_type_id smallint;
alter table "premium_policy" add column job_selection_type_id smallint;
alter table "premium_policy" add column job_item_group_selection_type_id smallint;
alter table "premium_policy" add column job_item_selection_type_id smallint;

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
	branch_id integer NOT NULL
);

CREATE TABLE premium_policy_department (
	id serial NOT NULL,
	premium_policy_id integer NOT NULL,
	department_id integer NOT NULL
);

alter table bank_account alter column institution drop NOT NULL;

alter table premium_policy alter column rate type numeric(9,4);
alter table premium_policy alter column accrual_rate type numeric(9,4);
alter table over_time_policy alter column rate type numeric(9,4);
alter table over_time_policy alter column accrual_rate type numeric(9,4);
