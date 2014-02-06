CREATE TABLE qualification (
  id serial  NOT NULL ,
  type_id integer  NOT NULL,
  company_id integer NOT NULL,
  group_id integer NOT NULL DEFAULT 0,
  name character varying NOT NULL,
  name_metaphone character varying,
  description text,
  created_date integer DEFAULT NULL,
  created_by integer  DEFAULT NULL,
  updated_date integer  DEFAULT NULL,
  updated_by integer  DEFAULT NULL,
  deleted_date integer  DEFAULT NULL,
  deleted_by integer  DEFAULT NULL,
  deleted smallint  DEFAULT 0 NOT NULL
);

CREATE TABLE  user_education (
  id serial  NOT NULL ,
  user_id integer NOT NULL,
  qualification_id integer NOT NULL,
  institute character varying NOT NULL,
  major character varying NOT NULL,
  minor character varying NOT NULL,
  graduate_date integer DEFAULT NULL,
  grade_score character varying DEFAULT NULL,
  start_date integer DEFAULT NULL,
  end_date integer DEFAULT NULL,
  created_date integer DEFAULT NULL,
  created_by integer DEFAULT NULL,
  updated_date integer DEFAULT NULL,
  updated_by integer DEFAULT NULL,
  deleted_date integer DEFAULT NULL,
  deleted_by integer DEFAULT NULL,
  deleted smallint  DEFAULT 0 NOT NULL
);

CREATE TABLE  user_license (
  id serial  NOT NULL ,
  user_id integer NOT NULL,
  qualification_id integer NOT NULL,
  license_number character varying NOT NULL,
  license_issued_date integer DEFAULT NULL,
  license_expiry_date integer DEFAULT NULL,
  created_date integer DEFAULT NULL,
  created_by integer DEFAULT NULL,
  updated_date integer DEFAULT NULL,
  updated_by integer DEFAULT NULL,
  deleted_date integer DEFAULT NULL,
  deleted_by integer DEFAULT NULL,
  deleted smallint  DEFAULT 0 NOT NULL
);

CREATE TABLE  user_skill (
  id serial  NOT NULL ,
  user_id integer NOT NULL DEFAULT 0,
  qualification_id integer NOT NULL,
  proficiency_id  integer NOT NULL,
  experience integer NOT NULL DEFAULT 0,
  description character varying NOT NULL DEFAULT '',
  first_used_date integer  DEFAULT NULL,
  last_used_date integer  DEFAULT NULL,
  enable_calc_experience smallint DEFAULT 0 NOT NULL,
  expiry_date integer  DEFAULT NULL,
  created_date integer  DEFAULT NULL,
  created_by integer  DEFAULT NULL,
  updated_date integer  DEFAULT NULL,
  updated_by integer  DEFAULT NULL,
  deleted_date integer  DEFAULT NULL,
  deleted_by integer  DEFAULT NULL,
  deleted smallint  DEFAULT 0 NOT NULL
);

CREATE TABLE user_language (
  id serial  NOT NULL ,
  user_id integer NOT NULL DEFAULT 0,
  qualification_id integer NOT NULL,
  fluency_id integer NOT NULL DEFAULT 0,
  competency_id integer NOT NULL DEFAULT 0,
  description character varying NOT NULL DEFAULT '',
  created_date integer DEFAULT NULL,
  created_by integer DEFAULT NULL,
  updated_date integer DEFAULT NULL,
  updated_by integer DEFAULT NULL,
  deleted_date integer DEFAULT NULL,
  deleted_by integer DEFAULT NULL,
  deleted smallint  DEFAULT 0 NOT NULL
);

CREATE TABLE user_membership (
  id serial  NOT NULL ,
  user_id integer NOT NULL DEFAULT 0,
  qualification_id integer NOT NULL,
  ownership_id integer NOT NULL DEFAULT 0,
  amount numeric(15,2) NOT NULL DEFAULT 0,
  currency_id integer NOT NULL,
  start_date integer  NOT NULL,
  renewal_date integer NOT NULL,
  created_date integer DEFAULT NULL,
  created_by integer DEFAULT NULL,
  updated_date integer DEFAULT NULL,
  updated_by integer DEFAULT NULL,
  deleted_date integer DEFAULT NULL,
  deleted_by integer DEFAULT NULL,
  deleted smallint  DEFAULT 0 NOT NULL
);

CREATE TABLE qualification_group (
  id serial  NOT NULL ,
  company_id integer NOT NULL,
  name character varying DEFAULT NULL,
  created_date integer DEFAULT NULL,
  created_by integer DEFAULT NULL,
  updated_date integer DEFAULT NULL,
  updated_by integer DEFAULT NULL,
  deleted_date integer DEFAULT NULL,
  deleted_by integer DEFAULT NULL,
  deleted smallint  DEFAULT 0 NOT NULL
);

CREATE TABLE qualification_group_tree (
    tree_id integer DEFAULT 0 NOT NULL,
    parent_id integer DEFAULT 0 NOT NULL,
    object_id integer DEFAULT 0 NOT NULL,
    left_id bigint DEFAULT 0 NOT NULL,
    right_id bigint DEFAULT 0 NOT NULL
);

CREATE TABLE kpi (
    id serial  NOT NULL ,
    company_id integer  NOT NULL,
    status_id integer NOT NULL,
    type_id integer NOT NULL,
    name character varying NOT NULL,
    description text,
    minimum_rate numeric(9,2),
    maximum_rate numeric(9,2),
    created_date integer DEFAULT NULL,
    created_by integer DEFAULT NULL,
    updated_date integer DEFAULT NULL,
    updated_by integer DEFAULT NULL,
    deleted_date integer DEFAULT NULL,
    deleted_by integer DEFAULT NULL,
    deleted smallint NOT NULL DEFAULT 0
);

CREATE TABLE user_review_control (
    id serial  NOT NULL ,
    user_id integer NOT NULL DEFAULT 0,
    reviewer_user_id integer NOT NULL DEFAULT 0,
    type_id integer NOT NULL,
    term_id integer NOT NULL,
    severity_id integer NOT NULL,
    status_id integer NOT NULL,
    start_date integer NOT NULL,
    end_date integer NOT NULL,
    due_date integer NOT NULL,
    rating numeric(9,2),
    note text DEFAULT NULL,
    created_date integer DEFAULT NULL,
    created_by integer DEFAULT NULL,
    updated_date integer DEFAULT NULL,
    updated_by integer DEFAULT NULL,
    deleted_date integer DEFAULT NULL,
    deleted_by integer DEFAULT NULL,
    deleted smallint NOT NULL DEFAULT 0
);

CREATE TABLE user_review (
    id serial  NOT NULL ,
    user_review_control_id integer NOT NULL,
    kpi_id integer NOT NULL,
    rating numeric(9,2),
    note text DEFAULT NULL,
    created_date integer DEFAULT NULL,
    created_by integer DEFAULT NULL,
    updated_date integer DEFAULT NULL,
    updated_by integer DEFAULT NULL,
    deleted_date integer DEFAULT NULL,
    deleted_by integer DEFAULT NULL,
    deleted smallint NOT NULL DEFAULT 0
);

CREATE TABLE kpi_group (
  id serial  NOT NULL ,
  company_id integer NOT NULL,
  name character varying DEFAULT NULL,
  created_date integer DEFAULT NULL,
  created_by integer DEFAULT NULL,
  updated_date integer DEFAULT NULL,
  updated_by integer DEFAULT NULL,
  deleted_date integer DEFAULT NULL,
  deleted_by integer DEFAULT NULL,
  deleted smallint  DEFAULT 0 NOT NULL
);

CREATE TABLE kpi_group_tree (
    tree_id integer DEFAULT 0 NOT NULL,
    parent_id integer DEFAULT 0 NOT NULL,
    object_id integer DEFAULT 0 NOT NULL,
    left_id bigint DEFAULT 0 NOT NULL,
    right_id bigint DEFAULT 0 NOT NULL
);

CREATE TABLE user_contact (
    id serial  NOT NULL ,
    user_id integer NOT NULL,
    status_id integer NOT NULL,
    type_id integer NOT NULL,
	ethnic_group_id integer NOT NULL,
    first_name character varying,
    middle_name character varying,
    last_name character varying,
    sex_id integer,
    address1 character varying,
    address2 character varying,
    city character varying,
    country character varying,
    province character varying,
    postal_code character varying,
    work_phone character varying,
    work_phone_ext character varying,
    home_phone character varying,
    mobile_phone character varying,
    fax_phone character varying,
    home_email character varying,
    work_email character varying,
    birth_date integer,
    sin character varying,
    note text,
    created_date integer  DEFAULT NULL,
    created_by integer  DEFAULT NULL,
    updated_date integer  DEFAULT NULL,
    updated_by integer  DEFAULT NULL,
    deleted_date integer  DEFAULT NULL,
    deleted_by integer  DEFAULT NULL,
    deleted smallint  DEFAULT 0 NOT NULL
);

CREATE TABLE ethnic_group (
    id serial NOT NULL,
    company_id integer DEFAULT 0 NOT NULL,
    name character varying,
    created_date integer  DEFAULT NULL,
    created_by integer  DEFAULT NULL,
    updated_date integer  DEFAULT NULL,
    updated_by integer  DEFAULT NULL,
    deleted_date integer  DEFAULT NULL,
    deleted_by integer  DEFAULT NULL,
    deleted smallint  DEFAULT 0 NOT NULL
);

CREATE TABLE company_setting (
    id serial NOT NULL,
    company_id integer NOT NULL,
    type_id integer NOT NULL,
    name character varying NOT NULL,
    value character varying,
    created_date integer,
    created_by integer,
    updated_date integer,
    updated_by integer,
    deleted_date integer,
    deleted_by integer,
    deleted smallint DEFAULT 0 NOT NULL
);

CREATE TABLE user_setting (
    id serial NOT NULL,
    user_id integer NOT NULL,
    type_id integer NOT NULL,
    name character varying NOT NULL,
    value character varying,
    created_date integer,
    created_by integer,
    updated_date integer,
    updated_by integer,
    deleted_date integer,
    deleted_by integer,
    deleted smallint DEFAULT 0 NOT NULL
);

CREATE INDEX qualification_company_id ON qualification USING btree (company_id);
CREATE INDEX qualification_type_id ON qualification USING btree (type_id);
CREATE INDEX qualification_group_company_id ON qualification_group USING btree (company_id);
CREATE INDEX qualification_group_tree_left_id_right_id ON qualification_group_tree USING btree (left_id, right_id);
CREATE INDEX qualification_group_tree_tree_id_object_id ON qualification_group_tree USING btree (tree_id, object_id);
CREATE INDEX qualification_group_tree_tree_id_parent_id ON qualification_group_tree USING btree (tree_id, parent_id);

CREATE INDEX user_education_user_id ON user_education USING btree (user_id);
CREATE INDEX user_license_user_id ON user_license USING btree (user_id);
CREATE INDEX user_skill_user_id ON user_skill USING btree (user_id);
CREATE INDEX user_language_user_id ON user_language USING btree (user_id);
CREATE INDEX user_membership_user_id ON user_membership USING btree (user_id);

CREATE INDEX user_education_qualification_id ON user_education USING btree (qualification_id);
CREATE INDEX user_license_qualification_id ON user_license USING btree (qualification_id);
CREATE INDEX user_skill_qualification_id ON user_skill USING btree (qualification_id);
CREATE INDEX user_language_qualification_id ON user_language USING btree (qualification_id);
CREATE INDEX user_membership_qualification_id ON user_membership USING btree (qualification_id);

CREATE INDEX kpi_company_id ON kpi USING btree (company_id);
CREATE INDEX kpi_group_company_id ON kpi_group USING btree (company_id);
CREATE INDEX kpi_group_tree_left_id_right_id ON kpi_group_tree USING btree (left_id, right_id);
CREATE INDEX kpi_group_tree_tree_id_object_id ON kpi_group_tree USING btree (tree_id, object_id);
CREATE INDEX kpi_group_tree_tree_id_parent_id ON kpi_group_tree USING btree (tree_id, parent_id);

CREATE INDEX user_review_kpi_id ON user_review USING btree (kpi_id);
CREATE INDEX user_review_control_id ON user_review USING btree (user_review_control_id);
CREATE INDEX user_review_control_user_id ON user_review_control USING btree (user_id);
CREATE INDEX user_contact_user_id ON user_contact USING btree (user_id);

CREATE INDEX ethnic_group_company_id ON ethnic_group USING btree (company_id); 

CREATE INDEX company_setting_company_id ON company_setting USING btree (company_id);
CREATE INDEX user_setting_user_id ON user_setting USING btree (user_id);

ALTER TABLE pay_stub_entry ADD COLUMN user_expense_id integer DEFAULT 0;
ALTER TABLE company ADD COLUMN migrate_url character varying;
ALTER TABLE users ADD COLUMN ethnic_group_id integer DEFAULT 0;
ALTER TABLE users ADD COLUMN default_job_id integer DEFAULT 0;
ALTER TABLE users ADD COLUMN default_job_item_id integer DEFAULT 0;
ALTER TABLE currency ADD COLUMN round_decimal_places smallint NOT NULL DEFAULT 2;
