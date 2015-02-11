
SET client_encoding = 'SQL_ASCII';
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

SET default_with_oids = false;


CREATE TABLE absence_policy (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	type_id integer NOT NULL,
	over_time boolean DEFAULT false NOT NULL,
	accrual_policy_id integer,
	premium_policy_id integer,
	pay_stub_entry_account_id integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE accrual (
	id serial NOT NULL,
	user_id integer NOT NULL,
	accrual_policy_id integer NOT NULL,
	type_id integer NOT NULL,
	user_date_total_id integer,
	time_stamp timestamp with time zone NOT NULL,
	amount integer DEFAULT 0 NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE accrual_balance (
	id serial NOT NULL,
	user_id integer NOT NULL,
	accrual_policy_id integer NOT NULL,
	balance integer DEFAULT 0 NOT NULL,
	banked_ytd integer DEFAULT 0 NOT NULL,
	used_ytd integer DEFAULT 0 NOT NULL,
	awarded_ytd integer DEFAULT 0 NOT NULL,
	created_date integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE accrual_policy (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	type_id integer NOT NULL,
	minimum integer,
	maximum integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);


CREATE TABLE authentication (
	id serial NOT NULL,
	session_id character varying(250) NOT NULL,
	user_id integer NOT NULL,
	ip_address character varying(250),
	created_date integer NOT NULL,
	updated_date integer
);


CREATE TABLE authorizations (
	id serial NOT NULL,
	object_type_id integer NOT NULL,
	object_id integer NOT NULL,
	authorized boolean DEFAULT false NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE bank_account (
	id serial NOT NULL,
	company_id integer NOT NULL,
	user_id integer,
	institution character varying(15) NOT NULL,
	transit character varying(15) NOT NULL,
	account character varying(50) NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE branch (
	id serial NOT NULL,
	company_id integer DEFAULT 0 NOT NULL,
	status_id integer NOT NULL,
	name character varying,
	address1 character varying,
	address2 character varying,
	city character varying,
	province character varying,
	country character varying,
	postal_code character varying,
	work_phone character varying,
	fax_phone character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE bread_crumb (
	id serial NOT NULL,
	user_id integer NOT NULL,
	name character varying,
	url character varying,
	created_date integer
);


CREATE TABLE company (
	id serial NOT NULL,
	parent_id integer DEFAULT 0 NOT NULL,
	status_id integer NOT NULL,
	product_edition_id integer NOT NULL,
	name character varying,
	short_name character varying(15),
	address1 character varying,
	address2 character varying,
	city character varying,
	province character varying,
	country character varying,
	postal_code character varying,
	work_phone character varying,
	fax_phone character varying,
	business_number character varying(250),
	originagor_id character varying(250),
	data_center_id character varying(250),
	admin_contact integer,
	billing_contact integer,
	support_contact integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE company_deduction (
	id serial NOT NULL,
	company_id integer NOT NULL,
	status_id integer NOT NULL,
	type_id integer NOT NULL,
	name character varying NOT NULL,
	calculation_id integer NOT NULL,
	calculation_order integer DEFAULT 0 NOT NULL,
	country character varying,
	province character varying,
	district character varying,
	company_value1 character varying,
	company_value2 character varying,
	user_value1 character varying,
	user_value2 character varying,
	user_value3 character varying,
	user_value4 character varying,
	user_value5 character varying,
	user_value6 character varying,
	user_value7 character varying,
	user_value8 character varying,
	user_value9 character varying,
	user_value10 character varying,
	lock_user_value1 boolean DEFAULT false NOT NULL,
	lock_user_value2 boolean DEFAULT false NOT NULL,
	lock_user_value3 boolean DEFAULT false NOT NULL,
	lock_user_value4 boolean DEFAULT false NOT NULL,
	lock_user_value5 boolean DEFAULT false NOT NULL,
	lock_user_value6 boolean DEFAULT false NOT NULL,
	lock_user_value7 boolean DEFAULT false NOT NULL,
	lock_user_value8 boolean DEFAULT false NOT NULL,
	lock_user_value9 boolean DEFAULT false NOT NULL,
	lock_user_value10 boolean DEFAULT false NOT NULL,
	pay_stub_entry_account_id integer NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE company_deduction_pay_stub_entry_account (
	id serial NOT NULL,
	company_deduction_id integer NOT NULL,
	type_id integer NOT NULL,
	pay_stub_entry_account_id integer NOT NULL
);



CREATE TABLE cron (
	id serial NOT NULL,
	status_id integer DEFAULT 10 NOT NULL,
	name character varying NOT NULL,
	"minute" character varying NOT NULL,
	"hour" character varying NOT NULL,
	day_of_month character varying NOT NULL,
	"month" character varying NOT NULL,
	day_of_week character varying NOT NULL,
	command character varying NOT NULL,
	last_run_date timestamp with time zone,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE department (
	id serial NOT NULL,
	company_id integer DEFAULT 0 NOT NULL,
	status_id integer NOT NULL,
	name character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE department_branch (
	id serial NOT NULL,
	branch_id integer DEFAULT 0 NOT NULL,
	department_id integer DEFAULT 0 NOT NULL
);



CREATE TABLE department_branch_user (
	id serial NOT NULL,
	department_branch_id integer NOT NULL,
	user_id integer NOT NULL
);


CREATE TABLE exception (
	id serial NOT NULL,
	user_date_id integer NOT NULL,
	exception_policy_id integer NOT NULL,
	punch_id integer,
	punch_control_id integer,
	type_id integer NOT NULL,
	enable_demerit boolean DEFAULT false NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE exception_policy (
	id serial NOT NULL,
	exception_policy_control_id integer NOT NULL,
	type_id character varying(3) NOT NULL,
	severity_id integer NOT NULL,
	grace integer,
	watch_window integer,
	demerit integer,
	active boolean DEFAULT false NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE exception_policy_control (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE help (
	id serial NOT NULL,
	type_id integer NOT NULL,
	status_id integer NOT NULL,
	heading character varying,
	body text NOT NULL,
	keywords character varying,
	private boolean DEFAULT false NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE help_group (
	id serial NOT NULL,
	help_group_control_id integer DEFAULT 0 NOT NULL,
	help_id integer DEFAULT 0 NOT NULL,
	order_value integer
);



CREATE TABLE help_group_control (
	id serial NOT NULL,
	script_name character varying,
	name character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE hierarchy_control (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	description character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE hierarchy_object_type (
	id serial NOT NULL,
	hierarchy_control_id integer NOT NULL,
	object_type_id integer NOT NULL
);



CREATE TABLE hierarchy_share (
	id serial NOT NULL,
	hierarchy_control_id integer DEFAULT 0 NOT NULL,
	user_id integer DEFAULT 0 NOT NULL
);



CREATE TABLE hierarchy_tree (
	tree_id integer DEFAULT 0 NOT NULL,
	parent_id integer DEFAULT 0 NOT NULL,
	object_id integer DEFAULT 0 NOT NULL,
	left_id bigint DEFAULT 0 NOT NULL,
	right_id bigint DEFAULT 0 NOT NULL
);



CREATE TABLE holiday (
	id serial NOT NULL,
	country character varying,
	province character varying,
	name character varying NOT NULL,
	"interval" integer,
	"day" integer,
	day_of_week integer,
	"month" integer
);



CREATE TABLE holiday_policy (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	type_id integer NOT NULL,
	default_schedule_status_id integer NOT NULL,
	minimum_employed_days integer NOT NULL,
	minimum_worked_period_days integer,
	minimum_worked_days integer,
	average_time_days integer,
	include_over_time boolean DEFAULT false NOT NULL,
	include_paid_absence_time boolean DEFAULT false NOT NULL,
	minimum_time integer,
	maximum_time integer,
	"time" integer,
	absence_policy_id integer,
	round_interval_policy_id integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	force_over_time_policy boolean DEFAULT false,
	average_time_worked_days boolean DEFAULT true
);



CREATE TABLE holiday_policy_recurring_holiday (
	id serial NOT NULL,
	holiday_policy_id integer DEFAULT 0 NOT NULL,
	recurring_holiday_id integer DEFAULT 0 NOT NULL
);



CREATE TABLE holidays (
	id serial NOT NULL,
	holiday_policy_id integer NOT NULL,
	date_stamp date NOT NULL,
	name character varying NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE system_log (
	id serial NOT NULL,
	user_id integer,
	object_id integer,
	table_name character varying,
	action_id integer,
	description text,
	date integer DEFAULT 0 NOT NULL
);



CREATE TABLE meal_policy (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	type_id integer NOT NULL,
	amount integer NOT NULL,
	trigger_time integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	start_window integer,
	window_length integer
);



CREATE TABLE message (
	id serial NOT NULL,
	parent_id integer NOT NULL,
	object_type_id integer NOT NULL,
	object_id integer NOT NULL,
	priority_id integer NOT NULL,
	status_id integer NOT NULL,
	status_date integer,
	subject character varying,
	body text,
	require_ack boolean DEFAULT false,
	ack boolean,
	ack_date integer,
	ack_by integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE other_field (
	id serial NOT NULL,
	company_id integer NOT NULL,
	type_id integer NOT NULL,
	other_id1 character varying,
	other_id2 character varying,
	other_id3 character varying,
	other_id4 character varying,
	other_id5 character varying,
	other_id6 character varying,
	other_id7 character varying,
	other_id8 character varying,
	other_id9 character varying,
	other_id10 character varying,
	required_other_id1 boolean DEFAULT false,
	required_other_id2 boolean DEFAULT false,
	required_other_id3 boolean DEFAULT false,
	required_other_id4 boolean DEFAULT false,
	required_other_id5 boolean DEFAULT false,
	required_other_id6 boolean DEFAULT false,
	required_other_id7 boolean DEFAULT false,
	required_other_id8 boolean DEFAULT false,
	required_other_id9 boolean DEFAULT false,
	required_other_id10 boolean DEFAULT false,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE over_time_policy (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	type_id integer NOT NULL,
	trigger_time integer NOT NULL,
	rate numeric(9,2) NOT NULL,
	accrual_policy_id integer,
	accrual_rate numeric(9,2) NOT NULL,
	pay_stub_entry_account_id integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE pay_period (
	id serial NOT NULL,
	company_id integer NOT NULL,
	pay_period_schedule_id integer NOT NULL,
	status_id integer NOT NULL,
	is_primary boolean DEFAULT false NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	start_date timestamp without time zone,
	end_date timestamp without time zone,
	transaction_date timestamp without time zone,
	advance_end_date timestamp without time zone,
	advance_transaction_date timestamp without time zone,
	tainted boolean DEFAULT false,
	tainted_by integer,
	tainted_date integer
);



CREATE TABLE pay_period_schedule (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	description character varying,
	type_id integer NOT NULL,
	primary_date_ldom boolean,
	primary_transaction_date_ldom boolean,
	primary_transaction_date_bd boolean,
	secondary_date_ldom boolean,
	secondary_transaction_date_ldom boolean,
	secondary_transaction_date_bd boolean,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	anchor_date timestamp without time zone,
	primary_date timestamp without time zone,
	primary_transaction_date timestamp without time zone,
	secondary_date timestamp without time zone,
	secondary_transaction_date timestamp without time zone,
	day_start_time integer,
	day_continuous_time integer,
	start_week_day_id integer
);



CREATE TABLE pay_period_schedule_user (
	id serial NOT NULL,
	pay_period_schedule_id integer DEFAULT 0 NOT NULL,
	user_id integer DEFAULT 0 NOT NULL
);



CREATE TABLE pay_stub (
	id serial NOT NULL,
	pay_period_id integer DEFAULT 0 NOT NULL,
	user_id integer DEFAULT 0 NOT NULL,
	status_id integer DEFAULT 0 NOT NULL,
	status_date integer,
	status_by integer,
	advance boolean DEFAULT false NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	start_date timestamp without time zone,
	end_date timestamp without time zone,
	transaction_date timestamp without time zone,
	tainted boolean DEFAULT false NOT NULL,
	"temp" boolean DEFAULT false
);



CREATE TABLE pay_stub_amendment (
	id serial NOT NULL,
	user_id integer NOT NULL,
	pay_stub_entry_name_id integer NOT NULL,
	status_id integer DEFAULT 10 NOT NULL,
	effective_date integer,
	rate numeric(9,2),
	units numeric(9,2),
	amount numeric(9,2) DEFAULT 0 NOT NULL,
	description character varying,
	authorized boolean DEFAULT false,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	recurring_ps_amendment_id integer,
	ytd_adjustment boolean DEFAULT false,
	type_id integer NOT NULL,
	percent_amount numeric(9,2),
	percent_amount_entry_name_id integer
);



CREATE TABLE pay_stub_entry (
	id serial NOT NULL,
	pay_stub_id integer NOT NULL,
	rate numeric(9,2) DEFAULT 0 NOT NULL,
	units numeric(9,2) DEFAULT 0 NOT NULL,
	ytd_units numeric(9,2) DEFAULT 0 NOT NULL,
	amount numeric(9,2) DEFAULT 0 NOT NULL,
	ytd_amount numeric(9,2) DEFAULT 0 NOT NULL,
	description character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	pay_stub_entry_name_id integer NOT NULL,
	pay_stub_amendment_id integer
);



CREATE TABLE pay_stub_entry_account (
	id serial NOT NULL,
	company_id integer NOT NULL,
	status_id integer NOT NULL,
	type_id integer NOT NULL,
	ps_order integer NOT NULL,
	name character varying NOT NULL,
	accrual_pay_stub_entry_account_id integer,
	debit_account character varying,
	credit_account character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE pay_stub_entry_account_link (
	id serial NOT NULL,
	company_id integer NOT NULL,
	total_gross integer,
	total_employee_deduction integer,
	total_employer_deduction integer,
	total_net_pay integer,
	regular_time integer,
	monthly_advance integer,
	monthly_advance_deduction integer,
	employee_cpp integer,
	employee_ei integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE permission (
	id serial NOT NULL,
	company_id integer,
	user_id integer,
	section character varying,
	name character varying,
	value character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE policy_group (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	meal_policy_id integer,
	exception_policy_control_id integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	holiday_policy_id integer
);



CREATE TABLE policy_group_over_time_policy (
	id serial NOT NULL,
	policy_group_id integer DEFAULT 0 NOT NULL,
	over_time_policy_id integer DEFAULT 0 NOT NULL
);



CREATE TABLE policy_group_premium_policy (
	id serial NOT NULL,
	policy_group_id integer DEFAULT 0 NOT NULL,
	premium_policy_id integer DEFAULT 0 NOT NULL
);



CREATE TABLE policy_group_round_interval_policy (
	id serial NOT NULL,
	policy_group_id integer DEFAULT 0 NOT NULL,
	round_interval_policy_id integer DEFAULT 0 NOT NULL
);



CREATE TABLE policy_group_user (
	id serial NOT NULL,
	policy_group_id integer DEFAULT 0 NOT NULL,
	user_id integer DEFAULT 0 NOT NULL
);



CREATE TABLE premium_policy (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	type_id integer NOT NULL,
	start_date timestamp without time zone,
	end_date timestamp without time zone,
	start_time time without time zone,
	end_time time without time zone,
	sun boolean DEFAULT false NOT NULL,
	mon boolean DEFAULT false NOT NULL,
	tue boolean DEFAULT false NOT NULL,
	wed boolean DEFAULT false NOT NULL,
	thu boolean DEFAULT false NOT NULL,
	fri boolean DEFAULT false NOT NULL,
	sat boolean DEFAULT false NOT NULL,
	pay_type_id integer NOT NULL,
	rate numeric(9,2) NOT NULL,
	accrual_policy_id integer,
	accrual_rate numeric(9,2),
	pay_stub_entry_account_id integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE punch (
	id serial NOT NULL,
	punch_control_id integer NOT NULL,
	station_id integer,
	type_id integer NOT NULL,
	status_id integer NOT NULL,
	time_stamp timestamp with time zone NOT NULL,
	original_time_stamp timestamp with time zone NOT NULL,
	actual_time_stamp timestamp with time zone NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	transfer boolean DEFAULT false
);



CREATE TABLE punch_control (
	id serial NOT NULL,
	user_date_id integer NOT NULL,
	branch_id integer,
	department_id integer,
	job_id integer,
	job_item_id integer,
	quantity numeric,
	bad_quantity numeric,
	total_time integer DEFAULT 0 NOT NULL,
	actual_total_time integer DEFAULT 0 NOT NULL,
	meal_policy_id integer,
	overlap boolean DEFAULT false NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE recurring_holiday (
	id serial NOT NULL,
	company_id integer NOT NULL,
	type_id integer NOT NULL,
	name character varying NOT NULL,
	easter boolean DEFAULT false NOT NULL,
	week_interval integer,
	day_of_week integer,
	day_of_month integer,
	month_int integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	pivot_day_direction_id integer
);



CREATE TABLE recurring_ps_amendment (
	id serial NOT NULL,
	company_id integer NOT NULL,
	status_id integer DEFAULT 10 NOT NULL,
	start_date integer NOT NULL,
	end_date integer,
	frequency_id integer NOT NULL,
	name character varying,
	description character varying,
	pay_stub_entry_name_id integer NOT NULL,
	rate numeric(9,2),
	units numeric(9,2),
	amount numeric(9,2),
	percent_amount numeric(9,2),
	percent_amount_entry_name_id integer,
	ps_amendment_description character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	type_id integer NOT NULL
);



CREATE TABLE recurring_ps_amendment_user (
	id serial NOT NULL,
	recurring_ps_amendment_id integer NOT NULL,
	user_id integer NOT NULL
);



CREATE TABLE recurring_schedule_control (
	id serial NOT NULL,
	company_id integer NOT NULL,
	recurring_schedule_template_control_id integer NOT NULL,
	start_week integer NOT NULL,
	start_date date NOT NULL,
	end_date date,
	auto_fill boolean DEFAULT false NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE recurring_schedule_template (
	id serial NOT NULL,
	recurring_schedule_template_control_id integer NOT NULL,
	week integer NOT NULL,
	sun boolean DEFAULT false NOT NULL,
	mon boolean DEFAULT false NOT NULL,
	tue boolean DEFAULT false NOT NULL,
	wed boolean DEFAULT false NOT NULL,
	thu boolean DEFAULT false NOT NULL,
	fri boolean DEFAULT false NOT NULL,
	sat boolean DEFAULT false NOT NULL,
	start_time timestamp with time zone NOT NULL,
	end_time timestamp with time zone NOT NULL,
	schedule_policy_id integer,
	branch_id integer,
	department_id integer,
	job_id integer,
	job_item_id integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE recurring_schedule_template_control (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	description character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE recurring_schedule_user (
	id serial NOT NULL,
	recurring_schedule_control_id integer DEFAULT 0 NOT NULL,
	user_id integer DEFAULT 0 NOT NULL
);



CREATE TABLE request (
	id serial NOT NULL,
	user_date_id integer NOT NULL,
	type_id integer NOT NULL,
	status_id integer NOT NULL,
	authorized boolean,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE roe (
	id serial NOT NULL,
	user_id integer NOT NULL,
	pay_period_type_id integer NOT NULL,
	code_id character varying NOT NULL,
	first_date integer,
	last_date integer,
	pay_period_end_date integer,
	recall_date integer,
	insurable_hours numeric NOT NULL,
	insurable_earnings numeric NOT NULL,
	vacation_pay numeric,
	serial character varying,
	comments character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE round_interval_policy (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	punch_type_id integer NOT NULL,
	round_type_id integer NOT NULL,
	"interval" integer NOT NULL,
	"strict" boolean DEFAULT false NOT NULL,
	grace integer,
	minimum integer,
	maximum integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE round_policy (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	description character varying,
	default_policy boolean DEFAULT false NOT NULL,
	strict_start boolean DEFAULT true NOT NULL,
	strict_lunch_start boolean DEFAULT true NOT NULL,
	strict_lunch_end boolean DEFAULT true NOT NULL,
	strict_end boolean DEFAULT true NOT NULL,
	round_grace_start integer,
	round_grace_lunch_start integer,
	round_grace_lunch_end integer,
	round_grace_end integer,
	round_start integer,
	round_lunch_start integer,
	round_lunch_end integer,
	round_end integer,
	round_type_id_start integer,
	round_type_id_lunch_start integer,
	round_type_id_lunch_end integer,
	round_type_id_end integer,
	round_lunch_total boolean DEFAULT false NOT NULL,
	round_total boolean DEFAULT false NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	enable_bank_time boolean DEFAULT false,
	over_time_default integer,
	under_time_default integer
);



CREATE TABLE schedule (
	id serial NOT NULL,
	user_date_id integer NOT NULL,
	status_id integer NOT NULL,
	start_time timestamp with time zone NOT NULL,
	end_time timestamp with time zone NOT NULL,
	schedule_policy_id integer,
	absence_policy_id integer,
	branch_id integer,
	department_id integer,
	job_id integer,
	job_item_id integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	total_time integer
);



CREATE TABLE schedule_policy (
	id serial NOT NULL,
	company_id integer NOT NULL,
	name character varying NOT NULL,
	meal_policy_id integer,
	over_time_policy_id integer,
	absence_policy_id integer,
	start_stop_window integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE station (
	id serial NOT NULL,
	company_id integer NOT NULL,
	status_id integer NOT NULL,
	type_id integer NOT NULL,
	station_id character varying NOT NULL,
	source character varying,
	description character varying NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	allowed_date integer
);



CREATE TABLE station_user (
	id serial NOT NULL,
	station_id integer DEFAULT 0 NOT NULL,
	user_id integer DEFAULT 0 NOT NULL
);



CREATE TABLE system_setting (
	id serial NOT NULL,
	name character varying NOT NULL,
	value character varying
);


CREATE TABLE "user" (
	id serial NOT NULL,
	company_id integer NOT NULL,
	status_id integer NOT NULL,
	user_name character varying NOT NULL,
	"password" character varying NOT NULL,
	password_reset_key character varying NULL,
	password_reset_date integer NULL,
	phone_id character varying,
	phone_password character varying,
	first_name character varying,
	middle_name character varying,
	last_name character varying,
	address1 character varying,
	address2 character varying,
	city character varying,
	province character varying,
	country character varying,
	postal_code character varying,
	work_phone character varying,
	work_phone_ext character varying,
	home_phone character varying,
	mobile_phone character varying,
	fax_phone character varying,
	home_email character varying,
	work_email character varying,
	birth_date integer,
	hire_date integer,
	sin character varying,
	sex_id integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	ibutton_id character varying,
	labor_standard_industry integer DEFAULT 0,
	title_id integer,
	default_branch_id integer,
	default_department_id integer,
	employee_number character varying,
	termination_date integer,
	note text,
	other_id1 character varying,
	other_id2 character varying,
	other_id3 character varying,
	other_id4 character varying,
	other_id5 character varying
);



CREATE TABLE user_date (
	id serial NOT NULL,
	user_id integer NOT NULL,
	pay_period_id integer NOT NULL,
	date_stamp date NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE user_date_total (
	id serial NOT NULL,
	user_date_id integer NOT NULL,
	status_id integer NOT NULL,
	type_id integer NOT NULL,
	punch_control_id integer,
	over_time_policy_id integer,
	absence_policy_id integer,
	premium_policy_id integer,
	branch_id integer,
	department_id integer,
	job_id integer,
	job_item_id integer,
	quantity numeric,
	bad_quantity numeric,
	start_time_stamp timestamp with time zone,
	end_time_stamp timestamp with time zone,
	total_time integer DEFAULT 0 NOT NULL,
	override boolean DEFAULT false NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	actual_total_time integer DEFAULT 0
);



CREATE TABLE user_deduction (
	id serial NOT NULL,
	user_id integer NOT NULL,
	company_deduction_id integer NOT NULL,
	user_value1 character varying,
	user_value2 character varying,
	user_value3 character varying,
	user_value4 character varying,
	user_value5 character varying,
	user_value6 character varying,
	user_value7 character varying,
	user_value8 character varying,
	user_value9 character varying,
	user_value10 character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE user_default (
	id serial NOT NULL,
	company_id integer NOT NULL,
	pay_period_schedule_id integer,
	policy_group_id integer,
	employee_number character varying,
	city character varying,
	province character varying,
	country character varying,
	work_email character varying,
	work_phone character varying,
	work_phone_ext character varying,
	hire_date integer,
	title_id integer,
	default_branch_id integer,
	default_department_id integer,
	date_format character varying,
	time_format character varying,
	time_unit_format character varying,
	time_zone character varying,
	items_per_page integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE user_default_company_deduction (
	id serial NOT NULL,
	user_default_id integer NOT NULL,
	company_deduction_id integer NOT NULL
);



CREATE TABLE user_generic_data (
	id serial NOT NULL,
	user_id integer,
	script character varying NOT NULL,
	name character varying NOT NULL,
	is_default boolean DEFAULT false NOT NULL,
	data text,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	company_id integer NOT NULL
);

CREATE TABLE user_pay_period_total (
	id serial NOT NULL,
	pay_period_id integer NOT NULL,
	user_id integer NOT NULL,
	schedule_total_time integer,
	schedule_bank_time integer,
	schedule_sick_time integer,
	schedule_vacation_time integer,
	schedule_statutory_time integer,
	schedule_over_time_1 integer,
	schedule_over_time_2 integer,
	actual_total_time integer,
	total_time integer,
	bank_time integer,
	sick_time integer,
	vacation_time integer,
	statutory_time integer,
	over_time_1 integer,
	over_time_2 integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	schedule_bank_time_2 integer,
	schedule_bank_time_3 integer,
	bank_time_2 integer,
	bank_time_3 integer,
	schedule_regular_time integer,
	schedule_payable_time integer,
	regular_time integer,
	payable_time integer
);



CREATE TABLE user_preference (
	id serial NOT NULL,
	user_id integer NOT NULL,
	date_format character varying NOT NULL,
	time_format character varying NOT NULL,
	time_unit_format character varying NOT NULL,
	time_zone character varying NOT NULL,
	items_per_page integer,
	timesheet_view integer,
	start_week_day integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE user_tax (
	id serial NOT NULL,
	user_id integer NOT NULL,
	federal_claim numeric NOT NULL,
	provincial_claim numeric NOT NULL,
	federal_additional_deduction numeric NOT NULL,
	wcb_rate numeric NOT NULL,
	ei_exempt boolean DEFAULT false NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	cpp_exempt boolean DEFAULT false,
	federal_tax_exempt boolean DEFAULT false,
	provincial_tax_exempt boolean DEFAULT false,
	vacation_rate numeric NOT NULL,
	release_vacation boolean DEFAULT false
);



CREATE TABLE user_title (
	id serial NOT NULL,
	company_id integer DEFAULT 0 NOT NULL,
	name character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);



CREATE TABLE user_wage (
	id serial NOT NULL,
	user_id integer NOT NULL,
	type_id integer NOT NULL,
	wage numeric(9,2) NOT NULL,
	effective_date date,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	weekly_time integer
);

CREATE TABLE pay_period_time_sheet_verify (
	id serial NOT NULL,
	pay_period_id integer NOT NULL,
	user_id integer NOT NULL,

	status_id integer NOT NULL,

	authorized boolean DEFAULT false NOT NULL,

	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL
);

CREATE TABLE company_user_count (
	id serial NOT NULL,
	company_id integer NOT NULL,
	date_stamp date NOT NULL,
	active_users integer NOT NULL,
	inactive_users integer NOT NULL,
	deleted_users integer NOT NULL,
	created_date integer
);

CREATE SEQUENCE user_hierarchy_seperator_id_seq
	INCREMENT BY 1
	NO MAXVALUE
	NO MINVALUE
	CACHE 1;

CREATE UNIQUE INDEX "absence_policy_id" ON "absence_policy" USING btree (id);
CREATE UNIQUE INDEX "accrual_id" ON "accrual" USING btree (id);
CREATE UNIQUE INDEX "accrual_balance_id" ON "accrual_balance" USING btree (id);
CREATE UNIQUE INDEX "accrual_policy_id" ON "accrual_policy" USING btree (id);
CREATE UNIQUE INDEX "authentication_id" ON "authentication" USING btree (id);
CREATE UNIQUE INDEX "authorizations_id" ON "authorizations" USING btree (id);
CREATE UNIQUE INDEX "bank_account_id" ON "bank_account" USING btree (id);
CREATE UNIQUE INDEX "branch_id" ON "branch" USING btree (id);
CREATE UNIQUE INDEX "bread_crumb_id" ON "bread_crumb" USING btree (id);
CREATE UNIQUE INDEX "company_id" ON "company" USING btree (id);
CREATE UNIQUE INDEX "company_deduction_id" ON "company_deduction" USING btree (id);
CREATE UNIQUE INDEX "company_deduction_pay_stub_entry_account_id" ON "company_deduction_pay_stub_entry_account" USING btree (id);
CREATE UNIQUE INDEX "company_user_count_id" ON "company_user_count" USING btree (id);
CREATE UNIQUE INDEX "cron_id" ON "cron" USING btree (id);
CREATE UNIQUE INDEX "department_id" ON "department" USING btree (id);
CREATE UNIQUE INDEX "department_branch_id" ON "department_branch" USING btree (id);
CREATE UNIQUE INDEX "department_branch_user_id" ON "department_branch_user" USING btree (id);
CREATE UNIQUE INDEX "exception_id" ON "exception" USING btree (id);
CREATE UNIQUE INDEX "exception_policy_id" ON "exception_policy" USING btree (id);
CREATE UNIQUE INDEX "exception_policy_control_id" ON "exception_policy_control" USING btree (id);
CREATE UNIQUE INDEX "help_id" ON "help" USING btree (id);
CREATE UNIQUE INDEX "help_group_id" ON "help_group" USING btree (id);
CREATE UNIQUE INDEX "help_group_control_id" ON "help_group_control" USING btree (id);
CREATE UNIQUE INDEX "hierarchy_control_id" ON "hierarchy_control" USING btree (id);
CREATE UNIQUE INDEX "hierarchy_object_type_id" ON "hierarchy_object_type" USING btree (id);
CREATE UNIQUE INDEX "hierarchy_share_id" ON "hierarchy_share" USING btree (id);
CREATE UNIQUE INDEX "holiday_id" ON "holiday" USING btree (id);
CREATE UNIQUE INDEX "holiday_policy_id" ON "holiday_policy" USING btree (id);
CREATE UNIQUE INDEX "holiday_policy_recurring_holiday_id" ON "holiday_policy_recurring_holiday" USING btree (id);
CREATE UNIQUE INDEX "holidays_id" ON "holidays" USING btree (id);
CREATE UNIQUE INDEX "system_log_id" ON "system_log" USING btree (id);
CREATE UNIQUE INDEX "meal_policy_id" ON "meal_policy" USING btree (id);
CREATE UNIQUE INDEX "message_id" ON "message" USING btree (id);
CREATE UNIQUE INDEX "other_field_id" ON "other_field" USING btree (id);
CREATE UNIQUE INDEX "over_time_policy_id" ON "over_time_policy" USING btree (id);
CREATE UNIQUE INDEX "pay_period_id" ON "pay_period" USING btree (id);
CREATE UNIQUE INDEX "pay_period_schedule_id" ON "pay_period_schedule" USING btree (id);
CREATE UNIQUE INDEX "pay_period_schedule_user_id" ON "pay_period_schedule_user" USING btree (id);
CREATE UNIQUE INDEX "pay_period_time_sheet_verify_id" ON "pay_period_time_sheet_verify" USING btree (id);
CREATE UNIQUE INDEX "pay_stub_id" ON "pay_stub" USING btree (id);
CREATE UNIQUE INDEX "pay_stub_amendment_id" ON "pay_stub_amendment" USING btree (id);
CREATE UNIQUE INDEX "pay_stub_entry_id" ON "pay_stub_entry" USING btree (id);
CREATE UNIQUE INDEX "pay_stub_entry_account_id" ON "pay_stub_entry_account" USING btree (id);
CREATE UNIQUE INDEX "pay_stub_entry_account_link_id" ON "pay_stub_entry_account_link" USING btree (id);
CREATE UNIQUE INDEX "permission_id" ON "permission" USING btree (id);
CREATE UNIQUE INDEX "policy_group_id" ON "policy_group" USING btree (id);
CREATE UNIQUE INDEX "policy_group_over_time_policy_id" ON "policy_group_over_time_policy" USING btree (id);
CREATE UNIQUE INDEX "policy_group_premium_policy_id" ON "policy_group_premium_policy" USING btree (id);
CREATE UNIQUE INDEX "policy_group_round_interval_policy_id" ON "policy_group_round_interval_policy" USING btree (id);
CREATE UNIQUE INDEX "policy_group_user_id" ON "policy_group_user" USING btree (id);
CREATE UNIQUE INDEX "premium_policy_id" ON "premium_policy" USING btree (id);
CREATE UNIQUE INDEX "punch_id" ON "punch" USING btree (id);
CREATE UNIQUE INDEX "punch_control_id" ON "punch_control" USING btree (id);
CREATE UNIQUE INDEX "recurring_holiday_id" ON "recurring_holiday" USING btree (id);
CREATE UNIQUE INDEX "recurring_ps_amendment_id" ON "recurring_ps_amendment" USING btree (id);
CREATE UNIQUE INDEX "recurring_ps_amendment_user_id" ON "recurring_ps_amendment_user" USING btree (id);
CREATE UNIQUE INDEX "recurring_schedule_control_id" ON "recurring_schedule_control" USING btree (id);
CREATE UNIQUE INDEX "recurring_schedule_template_id" ON "recurring_schedule_template" USING btree (id);
CREATE UNIQUE INDEX "recurring_schedule_template_control_id" ON "recurring_schedule_template_control" USING btree (id);
CREATE UNIQUE INDEX "recurring_schedule_user_id" ON "recurring_schedule_user" USING btree (id);
CREATE UNIQUE INDEX "request_id" ON "request" USING btree (id);
CREATE UNIQUE INDEX "roe_id" ON "roe" USING btree (id);
CREATE UNIQUE INDEX "round_interval_policy_id" ON "round_interval_policy" USING btree (id);
CREATE UNIQUE INDEX "round_policy_id" ON "round_policy" USING btree (id);
CREATE UNIQUE INDEX "schedule_id" ON "schedule" USING btree (id);
CREATE UNIQUE INDEX "schedule_policy_id" ON "schedule_policy" USING btree (id);
CREATE UNIQUE INDEX "station_id" ON "station" USING btree (id);
CREATE UNIQUE INDEX "station_user_id" ON "station_user" USING btree (id);
CREATE UNIQUE INDEX "system_setting_id" ON "system_setting" USING btree (id);
CREATE UNIQUE INDEX "user_id" ON "user" USING btree (id);
CREATE UNIQUE INDEX "user_date_id" ON "user_date" USING btree (id);
CREATE UNIQUE INDEX "user_date_total_id" ON "user_date_total" USING btree (id);
CREATE UNIQUE INDEX "user_deduction_id" ON "user_deduction" USING btree (id);
CREATE UNIQUE INDEX "user_default_id" ON "user_default" USING btree (id);
CREATE UNIQUE INDEX "user_default_company_deduction_id" ON "user_default_company_deduction" USING btree (id);
CREATE UNIQUE INDEX "user_generic_data_id" ON "user_generic_data" USING btree (id);
CREATE UNIQUE INDEX "user_pay_period_total_id" ON "user_pay_period_total" USING btree (id);
CREATE UNIQUE INDEX "user_preference_id" ON "user_preference" USING btree (id);
CREATE UNIQUE INDEX "user_tax_id" ON "user_tax" USING btree (id);
CREATE UNIQUE INDEX "user_title_id" ON "user_title" USING btree (id);
CREATE UNIQUE INDEX "user_wage_id" ON "user_wage" USING btree (id);

CREATE INDEX accrual_user_id ON accrual USING btree (user_id);
CREATE INDEX bread_crumb_user_id_name_key ON bread_crumb USING btree (user_id, name);
CREATE INDEX exception_user_date_id ON exception USING btree (user_date_id);
CREATE INDEX hierarchy_tree_left_id_right_id ON hierarchy_tree USING btree (left_id, right_id);
CREATE INDEX hierarchy_tree_tree_id_object_id ON hierarchy_tree USING btree (tree_id, object_id);
CREATE INDEX hierarchy_tree_tree_id_parent_id ON hierarchy_tree USING btree (tree_id, parent_id);
CREATE INDEX holidays_holiday_policy_id ON holidays USING btree (holiday_policy_id);
CREATE INDEX system_log_user_id_table_name_action_id ON system_log USING btree (user_id, table_name, action_id);
CREATE INDEX pay_period_schedule_user_pay_period_schedule_id ON pay_period_schedule_user USING btree (pay_period_schedule_id);
CREATE INDEX pay_stub_amendment_user_id ON pay_stub_amendment USING btree (user_id);
CREATE INDEX pay_stub_entry_pay_stub_id ON pay_stub_entry USING btree (pay_stub_id);
CREATE INDEX pay_stub_user_id ON pay_stub USING btree (user_id);
CREATE INDEX permission_company_id_user_id ON permission USING btree (company_id, user_id);
CREATE INDEX policy_group_user_policy_group_id ON policy_group_user USING btree (policy_group_id);
CREATE INDEX punch_control_user_date_id ON punch_control USING btree (user_date_id);
CREATE INDEX punch_punch_control_id ON punch USING btree (punch_control_id);
CREATE INDEX station_company_id ON station USING btree (company_id);
CREATE INDEX user_date_date_stamp ON user_date USING btree (date_stamp);
CREATE INDEX user_date_pay_period_id ON user_date USING btree (pay_period_id);
CREATE INDEX user_date_total_user_date_id ON user_date_total USING btree (user_date_id);
CREATE INDEX user_date_user_id ON user_date USING btree (user_id);
CREATE INDEX user_wage_user_id_effective_date ON user_wage USING btree (user_id, effective_date);



