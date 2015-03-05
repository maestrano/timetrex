alter table company add column password_policy_type_id smallint DEFAULT 0;
alter table company add column password_minimum_permission_level smallint DEFAULT 10;
alter table company add column password_minimum_strength smallint DEFAULT 3;
alter table company add column password_minimum_length smallint DEFAULT 8;
alter table company add column password_minimum_age smallint DEFAULT 0;
alter table company add column password_maximum_age smallint DEFAULT 365;

alter table company add column name_metaphone character varying;
alter table company add column longitude numeric(15,10);
alter table company add column latitude numeric(15,10);

alter table branch add column name_metaphone character varying;
alter table branch add column longitude numeric(15,10);
alter table branch add column latitude numeric(15,10);

alter table department add column name_metaphone character varying;

alter table users add column longitude numeric(15,10);
alter table users add column latitude numeric(15,10);
alter table users add column first_name_metaphone character varying;
alter table users add column last_name_metaphone character varying;
alter table users add column password_updated_date integer;
alter table users add column last_login_date integer;

alter table company add column other_id1 character varying;
alter table company add column other_id2 character varying;
alter table company add column other_id3 character varying;
alter table company add column other_id4 character varying;
alter table company add column other_id5 character varying;

alter table branch add column other_id1 character varying;
alter table branch add column other_id2 character varying;
alter table branch add column other_id3 character varying;
alter table branch add column other_id4 character varying;
alter table branch add column other_id5 character varying;

alter table department add column other_id1 character varying;
alter table department add column other_id2 character varying;
alter table department add column other_id3 character varying;
alter table department add column other_id4 character varying;
alter table department add column other_id5 character varying;

update pay_period_schedule set timesheet_verify_before_end_date = timesheet_verify_before_end_date*86400, timesheet_verify_before_transaction_date = timesheet_verify_before_transaction_date*86400 where ( timesheet_verify_before_end_date < 1000 AND timesheet_verify_before_end_date > -1000 ) AND ( timesheet_verify_before_transaction_date < 1000 AND timesheet_verify_before_transaction_date > -1000) AND deleted = 0;
	
CREATE TABLE company_generic_tag (
	id serial NOT NULL,
	company_id integer NOT NULL,
	object_type_id integer NOT NULL,
	name character varying NOT NULL,
	name_metaphone character varying,
	description character varying,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted integer DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX company_generic_tag_id ON company_generic_tag USING btree (id);
CREATE INDEX company_generic_tag_company_id ON company_generic_tag USING btree (company_id);
CREATE INDEX company_generic_tag_company_id_object_type_id ON company_generic_tag USING btree (company_id,object_type_id);

CREATE TABLE company_generic_tag_map (
	id serial NOT NULL,
	object_type_id integer NOT NULL,
	object_id integer NULL,
	tag_id integer NULL
);
CREATE UNIQUE INDEX company_generic_tag_map_id ON company_generic_tag USING btree (id);
CREATE INDEX company_generic_tag_map_object_type_id_object_id ON company_generic_tag_map USING btree (object_type_id,object_id);

CREATE UNIQUE INDEX permission_control_id ON permission_control USING btree (id);
CREATE UNIQUE INDEX accrual_policy_milestone_id ON accrual_policy_milestone USING btree (id);
CREATE UNIQUE INDEX break_policy_id ON break_policy USING btree (id);
CREATE UNIQUE INDEX currency_id ON currency USING btree (id);

CREATE UNIQUE INDEX permission_user_id ON permission_user USING btree (id);
CREATE UNIQUE INDEX premium_policy_branch_id ON premium_policy_branch USING btree (id);
CREATE UNIQUE INDEX premium_policy_department_id ON premium_policy_department USING btree (id);

CREATE UNIQUE INDEX user_group_id ON user_group USING btree (id);
CREATE INDEX user_group_tree_left_id_right_id ON user_group_tree USING btree (left_id, right_id);
CREATE INDEX user_group_tree_tree_id_object_id ON user_group_tree USING btree (tree_id, object_id);
CREATE INDEX user_group_tree_tree_id_parent_id ON user_group_tree USING btree (tree_id, parent_id);

DROP INDEX user_report_data_id;
DROP INDEX user_report_data_company_id;
DROP INDEX user_report_data_user_id;
CREATE UNIQUE INDEX user_report_data_id ON user_report_data USING btree (id);
CREATE INDEX user_report_data_company_id ON user_report_data USING btree (company_id);
CREATE INDEX user_report_data_user_id ON user_report_data USING btree (user_id);

CREATE INDEX user_generic_data_company_id ON user_generic_data USING btree (company_id);
CREATE INDEX user_generic_data_user_id ON user_generic_data USING btree (user_id);

update users set termination_date = NULL where termination_date = 0;
