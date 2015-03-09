CREATE TABLE absence_policy (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	name varchar(250) NOT NULL,
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
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE accrual (
	id integer AUTO_INCREMENT NOT NULL,
	user_id integer NOT NULL,
	accrual_policy_id integer NOT NULL,
	type_id integer NOT NULL,
	user_date_total_id integer,
	time_stamp timestamp NOT NULL,
	amount integer DEFAULT 0 NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE accrual_balance (
	id integer AUTO_INCREMENT NOT NULL,
	user_id integer NOT NULL,
	accrual_policy_id integer NOT NULL,
	balance integer DEFAULT 0 NOT NULL,
	banked_ytd integer DEFAULT 0 NOT NULL,
	used_ytd integer DEFAULT 0 NOT NULL,
	awarded_ytd integer DEFAULT 0 NOT NULL,
	created_date integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE accrual_policy (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	name varchar(250) NOT NULL,
	type_id integer NOT NULL,
	minimum integer,
	maximum integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;


CREATE TABLE authentication (
	id integer AUTO_INCREMENT NOT NULL,
	session_id varchar(250) NOT NULL,
	user_id integer NOT NULL,
	ip_address varchar(250),
	created_date integer NOT NULL,
	updated_date integer,
	PRIMARY KEY(id)
) ENGINE=InnoDB;


CREATE TABLE authorizations (
	id integer AUTO_INCREMENT NOT NULL,
	object_type_id integer NOT NULL,
	object_id integer NOT NULL,
	authorized boolean DEFAULT false NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE bank_account (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	user_id integer,
	institution varchar(15) NOT NULL,
	transit varchar(15) NOT NULL,
	account varchar(50) NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE branch (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer DEFAULT 0 NOT NULL,
	status_id integer NOT NULL,
	name varchar(250),
	address1 varchar(250),
	address2 varchar(250),
	city varchar(250),
	province varchar(250),
	country varchar(250),
	postal_code varchar(250),
	work_phone varchar(250),
	fax_phone varchar(250),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE bread_crumb (
	id integer AUTO_INCREMENT NOT NULL,
	user_id integer NOT NULL,
	name varchar(250),
	url varchar(250),
	created_date integer,
	PRIMARY KEY(id)
) ENGINE=InnoDB;


CREATE TABLE company (
	id integer AUTO_INCREMENT NOT NULL,
	parent_id integer DEFAULT 0 NOT NULL,
	status_id integer NOT NULL,
	product_edition_id integer NOT NULL,
	name varchar(250),
	short_name varchar(15),
	address1 varchar(250),
	address2 varchar(250),
	city varchar(250),
	province varchar(250),
	country varchar(250),
	postal_code varchar(250),
	work_phone varchar(250),
	fax_phone varchar(250),
	business_number varchar(250),
	originagor_id varchar(250),
	data_center_id varchar(250),
	admin_contact integer,
	billing_contact integer,
	support_contact integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE company_deduction (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	status_id integer NOT NULL,
	type_id integer NOT NULL,
	name varchar(250) NOT NULL,
	calculation_id integer NOT NULL,
	calculation_order integer DEFAULT 0 NOT NULL,
	country varchar(250),
	province varchar(250),
	district varchar(250),
	company_value1 varchar(250),
	company_value2 varchar(250),
	user_value1 varchar(250),
	user_value2 varchar(250),
	user_value3 varchar(250),
	user_value4 varchar(250),
	user_value5 varchar(250),
	user_value6 varchar(250),
	user_value7 varchar(250),
	user_value8 varchar(250),
	user_value9 varchar(250),
	user_value10 varchar(250),
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
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE company_deduction_pay_stub_entry_account (
	id integer AUTO_INCREMENT NOT NULL,
	company_deduction_id integer NOT NULL,
	type_id integer NOT NULL,
	pay_stub_entry_account_id integer NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE cron (
	id integer AUTO_INCREMENT NOT NULL,
	status_id integer DEFAULT 10 NOT NULL,
	name varchar(250) NOT NULL,
	minute varchar(250) NOT NULL,
	hour varchar(250) NOT NULL,
	day_of_month varchar(250) NOT NULL,
	month varchar(250) NOT NULL,
	day_of_week varchar(250) NOT NULL,
	command varchar(250) NOT NULL,
	last_run_date timestamp NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE department (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer DEFAULT 0 NOT NULL,
	status_id integer NOT NULL,
	name varchar(250),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE department_branch (
	id integer AUTO_INCREMENT NOT NULL,
	branch_id integer DEFAULT 0 NOT NULL,
	department_id integer DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE department_branch_user (
	id integer AUTO_INCREMENT NOT NULL,
	department_branch_id integer NOT NULL,
	user_id integer NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;


CREATE TABLE exception (
	id integer AUTO_INCREMENT NOT NULL,
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
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE exception_policy (
	id integer AUTO_INCREMENT NOT NULL,
	exception_policy_control_id integer NOT NULL,
	type_id varchar(3) NOT NULL,
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
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE exception_policy_control (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	name varchar(250) NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE help (
	id integer AUTO_INCREMENT NOT NULL,
	type_id integer NOT NULL,
	status_id integer NOT NULL,
	heading varchar(250),
	body text NOT NULL,
	keywords varchar(250),
	private boolean DEFAULT false NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE help_group (
	id integer AUTO_INCREMENT NOT NULL,
	help_group_control_id integer DEFAULT 0 NOT NULL,
	help_id integer DEFAULT 0 NOT NULL,
	order_value integer,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE help_group_control (
	id integer AUTO_INCREMENT NOT NULL,
	script_name varchar(250),
	name varchar(250),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE hierarchy_control (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	name varchar(250) NOT NULL,
	description varchar(250),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE hierarchy_object_type (
	id integer AUTO_INCREMENT NOT NULL,
	hierarchy_control_id integer NOT NULL,
	object_type_id integer NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE hierarchy_share (
	id integer AUTO_INCREMENT NOT NULL,
	hierarchy_control_id integer DEFAULT 0 NOT NULL,
	user_id integer DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE hierarchy_tree (
	tree_id integer DEFAULT 0 NOT NULL,
	parent_id integer DEFAULT 0 NOT NULL,
	object_id integer DEFAULT 0 NOT NULL,
	left_id bigint DEFAULT 0 NOT NULL,
	right_id bigint DEFAULT 0 NOT NULL
) ENGINE=InnoDB;


CREATE TABLE holiday_policy (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	name varchar(250) NOT NULL,
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
	time integer,
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
	average_time_worked_days boolean DEFAULT true,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE holiday_policy_recurring_holiday (
	id integer AUTO_INCREMENT NOT NULL,
	holiday_policy_id integer DEFAULT 0 NOT NULL,
	recurring_holiday_id integer DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE holidays (
	id integer AUTO_INCREMENT NOT NULL,
	holiday_policy_id integer NOT NULL,
	date_stamp date NOT NULL,
	name varchar(250) NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE system_log (
	id integer AUTO_INCREMENT NOT NULL,
	user_id integer,
	object_id integer,
	table_name varchar(250),
	action_id integer,
	description text,
	date integer DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE meal_policy (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	name varchar(250) NOT NULL,
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
	window_length integer,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE message (
	id integer AUTO_INCREMENT NOT NULL,
	parent_id integer NOT NULL,
	object_type_id integer NOT NULL,
	object_id integer NOT NULL,
	priority_id integer NOT NULL,
	status_id integer NOT NULL,
	status_date integer,
	subject varchar(250),
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
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE other_field (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	type_id integer NOT NULL,
	other_id1 varchar(250),
	other_id2 varchar(250),
	other_id3 varchar(250),
	other_id4 varchar(250),
	other_id5 varchar(250),
	other_id6 varchar(250),
	other_id7 varchar(250),
	other_id8 varchar(250),
	other_id9 varchar(250),
	other_id10 varchar(250),
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
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE over_time_policy (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	name varchar(250) NOT NULL,
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
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE pay_period (
	id integer AUTO_INCREMENT NOT NULL,
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
	start_date timestamp NULL,
	end_date timestamp NULL,
	transaction_date timestamp NULL,
	advance_end_date timestamp NULL,
	advance_transaction_date timestamp NULL,
	tainted boolean DEFAULT false,
	tainted_by integer,
	tainted_date integer,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE pay_period_schedule (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	name varchar(250) NOT NULL,
	description varchar(250),
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
	anchor_date timestamp NULL,
	primary_date timestamp NULL,
	primary_transaction_date timestamp NULL,
	secondary_date timestamp NULL,
	secondary_transaction_date timestamp NULL,
	day_start_time integer,
	day_continuous_time integer,
	start_week_day_id integer,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE pay_period_schedule_user (
	id integer AUTO_INCREMENT NOT NULL,
	pay_period_schedule_id integer DEFAULT 0 NOT NULL,
	user_id integer DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE pay_stub (
	id integer AUTO_INCREMENT NOT NULL,
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
	start_date timestamp NULL,
	end_date timestamp NULL,
	transaction_date timestamp NULL,
	tainted boolean DEFAULT false NOT NULL,
	temp boolean DEFAULT false,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE pay_stub_amendment (
	id integer AUTO_INCREMENT NOT NULL,
	user_id integer NOT NULL,
	pay_stub_entry_name_id integer NOT NULL,
	status_id integer DEFAULT 10 NOT NULL,
	effective_date integer,
	rate numeric(9,2),
	units numeric(9,2),
	amount numeric(9,2) DEFAULT 0 NOT NULL,
	description varchar(250),
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
	percent_amount_entry_name_id integer,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE pay_stub_entry (
	id integer AUTO_INCREMENT NOT NULL,
	pay_stub_id integer NOT NULL,
	rate numeric(9,2) DEFAULT 0 NOT NULL,
	units numeric(9,2) DEFAULT 0 NOT NULL,
	ytd_units numeric(9,2) DEFAULT 0 NOT NULL,
	amount numeric(9,2) DEFAULT 0 NOT NULL,
	ytd_amount numeric(9,2) DEFAULT 0 NOT NULL,
	description varchar(250),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	pay_stub_entry_name_id integer NOT NULL,
	pay_stub_amendment_id integer,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE pay_stub_entry_account (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	status_id integer NOT NULL,
	type_id integer NOT NULL,
	ps_order integer NOT NULL,
	name varchar(250) NOT NULL,
	accrual_pay_stub_entry_account_id integer,
	debit_account varchar(250),
	credit_account varchar(250),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE pay_stub_entry_account_link (
	id integer AUTO_INCREMENT NOT NULL,
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
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE permission (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer,
	user_id integer,
	section varchar(250),
	name varchar(250),
	value varchar(250),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE policy_group (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	name varchar(250) NOT NULL,
	meal_policy_id integer,
	exception_policy_control_id integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	holiday_policy_id integer,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE policy_group_over_time_policy (
	id integer AUTO_INCREMENT NOT NULL,
	policy_group_id integer DEFAULT 0 NOT NULL,
	over_time_policy_id integer DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE policy_group_premium_policy (
	id integer AUTO_INCREMENT NOT NULL,
	policy_group_id integer DEFAULT 0 NOT NULL,
	premium_policy_id integer DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE policy_group_round_interval_policy (
	id integer AUTO_INCREMENT NOT NULL,
	policy_group_id integer DEFAULT 0 NOT NULL,
	round_interval_policy_id integer DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE policy_group_user (
	id integer AUTO_INCREMENT NOT NULL,
	policy_group_id integer DEFAULT 0 NOT NULL,
	user_id integer DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE premium_policy (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	name varchar(250) NOT NULL,
	type_id integer NOT NULL,
	start_date timestamp NULL,
	end_date timestamp NULL,
	start_time time NULL,
	end_time time NULL,
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
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE punch (
	id integer AUTO_INCREMENT NOT NULL,
	punch_control_id integer NOT NULL,
	station_id integer,
	type_id integer NOT NULL,
	status_id integer NOT NULL,
	time_stamp timestamp NOT NULL,
	original_time_stamp timestamp NOT NULL,
	actual_time_stamp timestamp NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	transfer boolean DEFAULT false,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE punch_control (
	id integer AUTO_INCREMENT NOT NULL,
	user_date_id integer NOT NULL,
	branch_id integer,
	department_id integer,
	job_id integer,
	job_item_id integer,
	quantity numeric(9,2),
	bad_quantity numeric(9,2),
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
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE recurring_holiday (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	type_id integer NOT NULL,
	name varchar(250) NOT NULL,
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
	pivot_day_direction_id integer,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE recurring_ps_amendment (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	status_id integer DEFAULT 10 NOT NULL,
	start_date integer NOT NULL,
	end_date integer,
	frequency_id integer NOT NULL,
	name varchar(250),
	description varchar(250),
	pay_stub_entry_name_id integer NOT NULL,
	rate numeric(9,2),
	units numeric(9,2),
	amount numeric(9,2),
	percent_amount numeric(9,2),
	percent_amount_entry_name_id integer,
	ps_amendment_description varchar(250),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	type_id integer NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE recurring_ps_amendment_user (
	id integer AUTO_INCREMENT NOT NULL,
	recurring_ps_amendment_id integer NOT NULL,
	user_id integer NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE recurring_schedule_control (
	id integer AUTO_INCREMENT NOT NULL,
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
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE recurring_schedule_template (
	id integer AUTO_INCREMENT NOT NULL,
	recurring_schedule_template_control_id integer NOT NULL,
	week integer NOT NULL,
	sun boolean DEFAULT false NOT NULL,
	mon boolean DEFAULT false NOT NULL,
	tue boolean DEFAULT false NOT NULL,
	wed boolean DEFAULT false NOT NULL,
	thu boolean DEFAULT false NOT NULL,
	fri boolean DEFAULT false NOT NULL,
	sat boolean DEFAULT false NOT NULL,
	start_time timestamp NOT NULL,
	end_time timestamp NOT NULL,
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
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE recurring_schedule_template_control (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	name varchar(250) NOT NULL,
	description varchar(250),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE recurring_schedule_user (
	id integer AUTO_INCREMENT NOT NULL,
	recurring_schedule_control_id integer DEFAULT 0 NOT NULL,
	user_id integer DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE request (
	id integer AUTO_INCREMENT NOT NULL,
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
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE roe (
	id integer AUTO_INCREMENT NOT NULL,
	user_id integer NOT NULL,
	pay_period_type_id integer NOT NULL,
	code_id varchar(250) NOT NULL,
	first_date integer,
	last_date integer,
	pay_period_end_date integer,
	recall_date integer,
	insurable_hours numeric(9,2) NOT NULL,
	insurable_earnings numeric(9,2) NOT NULL,
	vacation_pay numeric(9,2),
	serial varchar(250),
	comments varchar(250),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE round_interval_policy (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	name varchar(250) NOT NULL,
	punch_type_id integer NOT NULL,
	round_type_id integer NOT NULL,
	round_interval integer NOT NULL,
	strict boolean DEFAULT false NOT NULL,
	grace integer,
	minimum integer,
	maximum integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE schedule (
	id integer AUTO_INCREMENT NOT NULL,
	user_date_id integer NOT NULL,
	status_id integer NOT NULL,
	start_time timestamp NOT NULL,
	end_time timestamp NOT NULL,
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
	total_time integer,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE schedule_policy (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	name varchar(250) NOT NULL,
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
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE station (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	status_id integer NOT NULL,
	type_id integer NOT NULL,
	station_id varchar(250) NOT NULL,
	source varchar(250),
	description varchar(250) NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	allowed_date integer,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE station_user (
	id integer AUTO_INCREMENT NOT NULL,
	station_id integer DEFAULT 0 NOT NULL,
	user_id integer DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE system_setting (
	id integer AUTO_INCREMENT NOT NULL,
	name varchar(250) NOT NULL,
	value varchar(250),
	PRIMARY KEY(id)
) ENGINE=InnoDB;


CREATE TABLE users (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	status_id integer NOT NULL,
	user_name varchar(250) NOT NULL,
	password varchar(250) NOT NULL,
	password_reset_key varchar(250) NULL,
	password_reset_date integer NULL,
	phone_id varchar(250),
	phone_password varchar(250),
	first_name varchar(250),
	middle_name varchar(250),
	last_name varchar(250),
	address1 varchar(250),
	address2 varchar(250),
	city varchar(250),
	province varchar(250),
	country varchar(250),
	postal_code varchar(250),
	work_phone varchar(250),
	work_phone_ext varchar(250),
	home_phone varchar(250),
	mobile_phone varchar(250),
	fax_phone varchar(250),
	home_email varchar(250),
	work_email varchar(250),
	birth_date integer,
	hire_date integer,
	sin varchar(250),
	sex_id integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	ibutton_id varchar(250),
	labor_standard_industry integer DEFAULT 0,
	title_id integer,
	default_branch_id integer,
	default_department_id integer,
	employee_number varchar(250),
	termination_date integer,
	note text,
	other_id1 varchar(250),
	other_id2 varchar(250),
	other_id3 varchar(250),
	other_id4 varchar(250),
	other_id5 varchar(250),
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE user_date (
	id integer AUTO_INCREMENT NOT NULL,
	user_id integer NOT NULL,
	pay_period_id integer NOT NULL,
	date_stamp date NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE user_date_total (
	id integer AUTO_INCREMENT NOT NULL,
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
	quantity numeric(9,2),
	bad_quantity numeric(9,2),
	start_time_stamp timestamp NULL,
	end_time_stamp timestamp NULL,
	total_time integer DEFAULT 0 NOT NULL,
	override boolean DEFAULT false NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	actual_total_time integer DEFAULT 0,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE user_deduction (
	id integer AUTO_INCREMENT NOT NULL,
	user_id integer NOT NULL,
	company_deduction_id integer NOT NULL,
	user_value1 varchar(250),
	user_value2 varchar(250),
	user_value3 varchar(250),
	user_value4 varchar(250),
	user_value5 varchar(250),
	user_value6 varchar(250),
	user_value7 varchar(250),
	user_value8 varchar(250),
	user_value9 varchar(250),
	user_value10 varchar(250),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE user_default (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	pay_period_schedule_id integer,
	policy_group_id integer,
	employee_number varchar(250),
	city varchar(250),
	province varchar(250),
	country varchar(250),
	work_email varchar(250),
	work_phone varchar(250),
	work_phone_ext varchar(250),
	hire_date integer,
	title_id integer,
	default_branch_id integer,
	default_department_id integer,
	date_format varchar(250),
	time_format varchar(250),
	time_unit_format varchar(250),
	time_zone varchar(250),
	items_per_page integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE user_default_company_deduction (
	id integer AUTO_INCREMENT NOT NULL,
	user_default_id integer NOT NULL,
	company_deduction_id integer NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE user_generic_data (
	id integer AUTO_INCREMENT NOT NULL,
	user_id integer,
	script varchar(250) NOT NULL,
	name varchar(250) NOT NULL,
	is_default boolean DEFAULT false NOT NULL,
	data text,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	company_id integer NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE user_pay_period_total (
	id integer AUTO_INCREMENT NOT NULL,
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
	payable_time integer,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE user_preference (
	id integer AUTO_INCREMENT NOT NULL,
	user_id integer NOT NULL,
	date_format varchar(250) NOT NULL,
	time_format varchar(250) NOT NULL,
	time_unit_format varchar(250) NOT NULL,
	time_zone varchar(250) NOT NULL,
	items_per_page integer,
	timesheet_view integer,
	start_week_day integer,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE user_tax (
	id integer AUTO_INCREMENT NOT NULL,
	user_id integer NOT NULL,
	federal_claim numeric(9,2) NOT NULL,
	provincial_claim numeric(9,2) NOT NULL,
	federal_additional_deduction numeric(9,2) NOT NULL,
	wcb_rate numeric(9,2) NOT NULL,
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
	vacation_rate numeric(9,2) NOT NULL,
	release_vacation boolean DEFAULT false,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE user_title (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer DEFAULT 0 NOT NULL,
	name varchar(250),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;



CREATE TABLE user_wage (
	id integer AUTO_INCREMENT NOT NULL,
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
	weekly_time integer,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE pay_period_time_sheet_verify (
	id integer AUTO_INCREMENT NOT NULL,
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
	deleted boolean DEFAULT false NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE company_user_count (
	id integer AUTO_INCREMENT NOT NULL,
	company_id integer NOT NULL,
	date_stamp date NOT NULL,
	active_users integer NOT NULL,
	inactive_users integer NOT NULL,
	deleted_users integer NOT NULL,
	created_date integer,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE UNIQUE INDEX absence_policy_id ON absence_policy(id);
CREATE UNIQUE INDEX accrual_id ON accrual(id);
CREATE UNIQUE INDEX accrual_balance_id ON accrual_balance(id);
CREATE UNIQUE INDEX accrual_policy_id ON accrual_policy(id);
CREATE UNIQUE INDEX authentication_id ON authentication(id);
CREATE UNIQUE INDEX authorizations_id ON authorizations(id);
CREATE UNIQUE INDEX bank_account_id ON bank_account(id);
CREATE UNIQUE INDEX branch_id ON branch(id);
CREATE UNIQUE INDEX bread_crumb_id ON bread_crumb(id);
CREATE UNIQUE INDEX company_id ON company(id);
CREATE UNIQUE INDEX company_deduction_id ON company_deduction(id);
CREATE UNIQUE INDEX company_deduction_pay_stub_entry_account_id ON company_deduction_pay_stub_entry_account(id);
CREATE UNIQUE INDEX company_user_count_id ON company_user_count(id);
CREATE UNIQUE INDEX cron_id ON cron(id);
CREATE UNIQUE INDEX department_id ON department(id);
CREATE UNIQUE INDEX department_branch_id ON department_branch(id);
CREATE UNIQUE INDEX department_branch_user_id ON department_branch_user(id);
CREATE UNIQUE INDEX exception_id ON exception(id);
CREATE UNIQUE INDEX exception_policy_id ON exception_policy(id);
CREATE UNIQUE INDEX exception_policy_control_id ON exception_policy_control(id);
CREATE UNIQUE INDEX help_id ON help(id);
CREATE UNIQUE INDEX help_group_id ON help_group(id);
CREATE UNIQUE INDEX help_group_control_id ON help_group_control(id);
CREATE UNIQUE INDEX hierarchy_control_id ON hierarchy_control(id);
CREATE UNIQUE INDEX hierarchy_object_type_id ON hierarchy_object_type(id);
CREATE UNIQUE INDEX hierarchy_share_id ON hierarchy_share(id);
CREATE UNIQUE INDEX holiday_policy_id ON holiday_policy(id);
CREATE UNIQUE INDEX holiday_policy_recurring_holiday_id ON holiday_policy_recurring_holiday(id);
CREATE UNIQUE INDEX holidays_id ON holidays(id);
CREATE UNIQUE INDEX system_log_id ON system_log(id);
CREATE UNIQUE INDEX meal_policy_id ON meal_policy(id);
CREATE UNIQUE INDEX message_id ON message(id);
CREATE UNIQUE INDEX other_field_id ON other_field(id);
CREATE UNIQUE INDEX over_time_policy_id ON over_time_policy(id);
CREATE UNIQUE INDEX pay_period_id ON pay_period(id);
CREATE UNIQUE INDEX pay_period_schedule_id ON pay_period_schedule(id);
CREATE UNIQUE INDEX pay_period_schedule_user_id ON pay_period_schedule_user(id);
CREATE UNIQUE INDEX pay_period_time_sheet_verify_id ON pay_period_time_sheet_verify(id);
CREATE UNIQUE INDEX pay_stub_id ON pay_stub(id);
CREATE UNIQUE INDEX pay_stub_amendment_id ON pay_stub_amendment(id);
CREATE UNIQUE INDEX pay_stub_entry_id ON pay_stub_entry(id);
CREATE UNIQUE INDEX pay_stub_entry_account_id ON pay_stub_entry_account(id);
CREATE UNIQUE INDEX pay_stub_entry_account_link_id ON pay_stub_entry_account_link(id);
CREATE UNIQUE INDEX permission_id ON permission(id);
CREATE UNIQUE INDEX policy_group_id ON policy_group(id);
CREATE UNIQUE INDEX policy_group_over_time_policy_id ON policy_group_over_time_policy(id);
CREATE UNIQUE INDEX policy_group_premium_policy_id ON policy_group_premium_policy(id);
CREATE UNIQUE INDEX policy_group_round_interval_policy_id ON policy_group_round_interval_policy(id);
CREATE UNIQUE INDEX policy_group_user_id ON policy_group_user(id);
CREATE UNIQUE INDEX premium_policy_id ON premium_policy(id);
CREATE UNIQUE INDEX punch_id ON punch(id);
CREATE UNIQUE INDEX punch_control_id ON punch_control(id);
CREATE UNIQUE INDEX recurring_holiday_id ON recurring_holiday(id);
CREATE UNIQUE INDEX recurring_ps_amendment_id ON recurring_ps_amendment(id);
CREATE UNIQUE INDEX recurring_ps_amendment_user_id ON recurring_ps_amendment_user(id);
CREATE UNIQUE INDEX recurring_schedule_control_id ON recurring_schedule_control(id);
CREATE UNIQUE INDEX recurring_schedule_template_id ON recurring_schedule_template(id);
CREATE UNIQUE INDEX recurring_schedule_template_control_id ON recurring_schedule_template_control(id);
CREATE UNIQUE INDEX recurring_schedule_user_id ON recurring_schedule_user(id);
CREATE UNIQUE INDEX request_id ON request(id);
CREATE UNIQUE INDEX roe_id ON roe(id);
CREATE UNIQUE INDEX round_interval_policy_id ON round_interval_policy(id);
CREATE UNIQUE INDEX schedule_id ON schedule(id);
CREATE UNIQUE INDEX schedule_policy_id ON schedule_policy(id);
CREATE UNIQUE INDEX station_id ON station(id);
CREATE UNIQUE INDEX station_user_id ON station_user(id);
CREATE UNIQUE INDEX system_setting_id ON system_setting(id);
CREATE UNIQUE INDEX user_id ON users(id);
CREATE UNIQUE INDEX user_date_id ON user_date(id);
CREATE UNIQUE INDEX user_date_total_id ON user_date_total(id);
CREATE UNIQUE INDEX user_deduction_id ON user_deduction(id);
CREATE UNIQUE INDEX user_default_id ON user_default(id);
CREATE UNIQUE INDEX user_default_company_deduction_id ON user_default_company_deduction(id);
CREATE UNIQUE INDEX user_generic_data_id ON user_generic_data(id);
CREATE UNIQUE INDEX user_pay_period_total_id ON user_pay_period_total(id);
CREATE UNIQUE INDEX user_preference_id ON user_preference(id);
CREATE UNIQUE INDEX user_tax_id ON user_tax(id);
CREATE UNIQUE INDEX user_title_id ON user_title(id);
CREATE UNIQUE INDEX user_wage_id ON user_wage(id);

CREATE INDEX accrual_user_id ON accrual(user_id);
CREATE INDEX bread_crumb_user_id_name_key ON bread_crumb(user_id, name);
CREATE INDEX exception_user_date_id ON exception(user_date_id);
CREATE INDEX hierarchy_tree_left_id_right_id ON hierarchy_tree(left_id, right_id);
CREATE INDEX hierarchy_tree_tree_id_object_id ON hierarchy_tree(tree_id, object_id);
CREATE INDEX hierarchy_tree_tree_id_parent_id ON hierarchy_tree(tree_id, parent_id);
CREATE INDEX holidays_holiday_policy_id ON holidays(holiday_policy_id);
CREATE INDEX system_log_user_id_table_name_action_id ON system_log(user_id, table_name, action_id);
CREATE INDEX pay_period_schedule_user_pay_period_schedule_id ON pay_period_schedule_user(pay_period_schedule_id);
CREATE INDEX pay_stub_amendment_user_id ON pay_stub_amendment(user_id);
CREATE INDEX pay_stub_entry_pay_stub_id ON pay_stub_entry(pay_stub_id);
CREATE INDEX pay_stub_user_id ON pay_stub(user_id);
CREATE INDEX permission_company_id_user_id ON permission(company_id, user_id);
CREATE INDEX policy_group_user_policy_group_id ON policy_group_user(policy_group_id);
CREATE INDEX punch_control_user_date_id ON punch_control(user_date_id);
CREATE INDEX punch_punch_control_id ON punch(punch_control_id);
CREATE INDEX station_company_id ON station(company_id);
CREATE INDEX user_date_date_stamp ON user_date(date_stamp);
CREATE INDEX user_date_pay_period_id ON user_date(pay_period_id);
CREATE INDEX user_date_total_user_date_id ON user_date_total(user_date_id);
CREATE INDEX user_date_user_id ON user_date(user_id);
CREATE INDEX user_wage_user_id_effective_date ON user_wage(user_id, effective_date);
