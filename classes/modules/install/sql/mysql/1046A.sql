alter table company add column password_policy_type_id smallint DEFAULT 0;
alter table company add column password_minimum_permission_level smallint DEFAULT 10;
alter table company add column password_minimum_strength smallint DEFAULT 3;
alter table company add column password_minimum_length smallint DEFAULT 8;
alter table company add column password_minimum_age smallint DEFAULT 0;
alter table company add column password_maximum_age smallint DEFAULT 365;

alter table company add column name_metaphone varchar(250);
alter table company add column longitude numeric(15,10);
alter table company add column latitude numeric(15,10);

alter table branch add column name_metaphone varchar(250);
alter table branch add column longitude numeric(15,10);
alter table branch add column latitude numeric(15,10);

alter table department add column name_metaphone varchar(250);

alter table users add column longitude numeric(15,10);
alter table users add column latitude numeric(15,10);
alter table users add column first_name_metaphone varchar(250);
alter table users add column last_name_metaphone varchar(250);
alter table users add column password_updated_date integer;
alter table users add column last_login_date integer;

alter table company add column other_id1 varchar(250);
alter table company add column other_id2 varchar(250);
alter table company add column other_id3 varchar(250);
alter table company add column other_id4 varchar(250);
alter table company add column other_id5 varchar(250);

alter table branch add column other_id1 varchar(250);
alter table branch add column other_id2 varchar(250);
alter table branch add column other_id3 varchar(250);
alter table branch add column other_id4 varchar(250);
alter table branch add column other_id5 varchar(250);

alter table department add column other_id1 varchar(250);
alter table department add column other_id2 varchar(250);
alter table department add column other_id3 varchar(250);
alter table department add column other_id4 varchar(250);
alter table department add column other_id5 varchar(250);

update pay_period_schedule set timesheet_verify_before_end_date = timesheet_verify_before_end_date*86400, timesheet_verify_before_transaction_date = timesheet_verify_before_transaction_date*86400 where ( timesheet_verify_before_end_date < 1000 AND timesheet_verify_before_end_date > -1000 ) AND ( timesheet_verify_before_transaction_date < 1000 AND timesheet_verify_before_transaction_date > -1000) AND deleted = 0;
	
CREATE TABLE company_generic_tag (
	id serial NOT NULL,
	company_id integer NOT NULL,
	object_type_id integer NOT NULL,
	name varchar(250) NOT NULL,
	name_metaphone varchar(250),
	description varchar(250),
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted integer DEFAULT 0 NOT NULL
) Engine=InnoDB;
CREATE UNIQUE INDEX company_generic_tag_id ON company_generic_tag(id);
CREATE INDEX company_generic_tag_company_id ON company_generic_tag(company_id);
CREATE INDEX company_generic_tag_company_id_object_type_id ON company_generic_tag(company_id,object_type_id);

CREATE TABLE company_generic_tag_map (
	id serial NOT NULL,
	object_type_id integer NOT NULL,
	object_id integer NULL,
	tag_id integer NULL
) Engine=InnoDB;
CREATE UNIQUE INDEX company_generic_tag_map_id ON company_generic_tag(id);
CREATE INDEX company_generic_tag_map_object_type_id_object_id ON company_generic_tag_map(object_type_id,object_id);

CREATE UNIQUE INDEX permission_control_id ON permission_control(id);
CREATE UNIQUE INDEX accrual_policy_milestone_id ON accrual_policy_milestone(id);
CREATE UNIQUE INDEX break_policy_id ON break_policy(id);
CREATE UNIQUE INDEX currency_id ON currency(id);

CREATE UNIQUE INDEX permission_user_id ON permission_user(id);
CREATE UNIQUE INDEX premium_policy_branch_id ON premium_policy_branch(id);
CREATE UNIQUE INDEX premium_policy_department_id ON premium_policy_department(id);

CREATE UNIQUE INDEX user_group_id ON user_group(id);
CREATE INDEX user_group_tree_left_id_right_id ON user_group_tree(left_id, right_id);
CREATE INDEX user_group_tree_tree_id_object_id ON user_group_tree(tree_id, object_id);
CREATE INDEX user_group_tree_tree_id_parent_id ON user_group_tree(tree_id, parent_id);

DROP INDEX user_report_data_id ON user_generic_data;
DROP INDEX user_report_data_company_id ON user_generic_data;
DROP INDEX user_report_data_user_id ON user_generic_data;
CREATE UNIQUE INDEX user_report_data_id ON user_report_data(id);
CREATE INDEX user_report_data_company_id ON user_report_data(company_id);
CREATE INDEX user_report_data_user_id ON user_report_data(user_id);

CREATE INDEX user_generic_data_company_id ON user_generic_data(company_id);
CREATE INDEX user_generic_data_user_id ON user_generic_data(user_id);

ALTER TABLE wage_group ENGINE=InnoDB;
ALTER TABLE company_generic_map ENGINE=InnoDB;
ALTER TABLE break_policy ENGINE=InnoDB;
ALTER TABLE hierarchy_level ENGINE=InnoDB;
ALTER TABLE hierarchy_user ENGINE=InnoDB;
ALTER TABLE message_recipient ENGINE=InnoDB;
ALTER TABLE message_sender ENGINE=InnoDB;
ALTER TABLE message_control ENGINE=InnoDB;
ALTER TABLE system_log_detail ENGINE=InnoDB;
ALTER TABLE user_report_data ENGINE=InnoDB;

update users set termination_date = NULL where termination_date = 0;

