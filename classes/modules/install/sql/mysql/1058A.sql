CREATE TABLE  qualification (
  id integer  NOT NULL AUTO_INCREMENT,
  type_id integer  NOT NULL,
  company_id integer NOT NULL,
  group_id integer NOT NULL DEFAULT 0,
  name varchar(100) NOT NULL,
  name_metaphone varchar(250),
  description text,
  created_date integer  DEFAULT NULL,
  created_by integer  DEFAULT NULL,
  updated_date integer  DEFAULT NULL,
  updated_by integer  DEFAULT NULL,
  deleted_date integer  DEFAULT NULL,
  deleted_by integer  DEFAULT NULL,
  deleted smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE  user_education (
  id integer  NOT NULL AUTO_INCREMENT,
  user_id integer NOT NULL,
  qualification_id integer NOT NULL,
  institute varchar(100) NOT NULL,
  major varchar(100) NOT NULL,
  minor varchar(100) NOT NULL,
  graduate_date integer DEFAULT NULL,
  grade_score varchar(50) DEFAULT NULL,
  start_date integer DEFAULT NULL,
  end_date integer DEFAULT NULL,
  created_date integer DEFAULT NULL,
  created_by integer DEFAULT NULL,
  updated_date integer DEFAULT NULL,
  updated_by integer DEFAULT NULL,
  deleted_date integer DEFAULT NULL,
  deleted_by integer DEFAULT NULL,
  deleted smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE  user_license (
  id integer  NOT NULL AUTO_INCREMENT,
  user_id integer NOT NULL,
  qualification_id integer NOT NULL,
  license_number varchar(50) NOT NULL,
  license_issued_date integer DEFAULT NULL,
  license_expiry_date integer DEFAULT NULL,
  created_date integer DEFAULT NULL,
  created_by integer DEFAULT NULL,
  updated_date integer DEFAULT NULL,
  updated_by integer DEFAULT NULL,
  deleted_date integer DEFAULT NULL,
  deleted_by integer DEFAULT NULL,
  deleted smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE  user_skill (
  id integer  NOT NULL AUTO_INCREMENT,
  user_id integer NOT NULL DEFAULT 0,
  qualification_id integer NOT NULL,
  proficiency_id  integer NOT NULL,
  experience integer NOT NULL DEFAULT 0,
  description varchar(100) NOT NULL DEFAULT '',
  first_used_date integer  DEFAULT NULL,
  last_used_date integer DEFAULT NULL,
  enable_calc_experience smallint(6) NOT NULL DEFAULT 0,
  expiry_date  integer DEFAULT NULL,
  created_date integer DEFAULT NULL,
  created_by integer DEFAULT NULL,
  updated_date integer DEFAULT NULL,
  updated_by integer DEFAULT NULL,
  deleted_date integer DEFAULT NULL,
  deleted_by integer DEFAULT NULL,
  deleted smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE user_language (
  id integer NOT NULL AUTO_INCREMENT,
  user_id integer NOT NULL DEFAULT 0,
  qualification_id integer NOT NULL,
  fluency_id integer NOT NULL DEFAULT 0,
  competency_id integer NOT NULL DEFAULT 0,
  description varchar(100) NOT NULL DEFAULT '',
  created_date integer DEFAULT NULL,
  created_by integer DEFAULT NULL,
  updated_date integer DEFAULT NULL,
  updated_by integer DEFAULT NULL,
  deleted_date integer DEFAULT NULL,
  deleted_by integer DEFAULT NULL,
  deleted smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE user_membership (
  id integer NOT NULL AUTO_INCREMENT,
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
  deleted smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE qualification_group (
  id integer NOT NULL AUTO_INCREMENT,
  company_id integer NOT NULL,
  name varchar(100) DEFAULT NULL,
  created_date integer DEFAULT NULL,
  created_by integer DEFAULT NULL,
  updated_date integer DEFAULT NULL,
  updated_by integer DEFAULT NULL,
  deleted_date integer DEFAULT NULL,
  deleted_by integer DEFAULT NULL,
  deleted smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE qualification_group_tree (
    tree_id integer DEFAULT 0 NOT NULL,
    parent_id integer DEFAULT 0 NOT NULL,
    object_id integer DEFAULT 0 NOT NULL,
    left_id bigint DEFAULT 0 NOT NULL,
    right_id bigint DEFAULT 0 NOT NULL
) ENGINE=InnoDB;

CREATE TABLE kpi (
    id integer  NOT NULL AUTO_INCREMENT,
    company_id integer  NOT NULL,
    status_id integer NOT NULL,
    type_id integer NOT NULL,
    name varchar(100) NOT NULL,
    description text,
    minimum_rate numeric(9,2),
    maximum_rate numeric(9,2),
    created_date integer DEFAULT NULL,
    created_by integer DEFAULT NULL,
    updated_date integer DEFAULT NULL,
    updated_by integer DEFAULT NULL,
    deleted_date integer DEFAULT NULL,
    deleted_by integer DEFAULT NULL,
    deleted smallint(6) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE user_review_control (
    id integer  NOT NULL AUTO_INCREMENT,
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
    deleted smallint(6) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE user_review (
    id integer  NOT NULL AUTO_INCREMENT,
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
    deleted smallint(6) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE kpi_group (
  id integer NOT NULL AUTO_INCREMENT,
  company_id integer NOT NULL,
  name varchar(100) DEFAULT NULL,
  created_date integer DEFAULT NULL,
  created_by integer DEFAULT NULL,
  updated_date integer DEFAULT NULL,
  updated_by integer DEFAULT NULL,
  deleted_date integer DEFAULT NULL,
  deleted_by integer DEFAULT NULL,
  deleted smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE kpi_group_tree (
    tree_id integer DEFAULT 0 NOT NULL,
    parent_id integer DEFAULT 0 NOT NULL,
    object_id integer DEFAULT 0 NOT NULL,
    left_id bigint DEFAULT 0 NOT NULL,
    right_id bigint DEFAULT 0 NOT NULL
) ENGINE=InnoDB;

CREATE TABLE user_contact (
    id integer AUTO_INCREMENT NOT NULL,
    user_id integer NOT NULL,
    status_id integer NOT NULL,
    type_id integer NOT NULL,
	ethnic_group_id integer NOT NULL,
    first_name varchar(250),
    middle_name varchar(250),
    last_name varchar(250),
    sex_id integer,
    address1 varchar(250),
    address2 varchar(250),
    city varchar(250),
    country varchar(250),
    province varchar(250),
    postal_code varchar(250),
    work_phone varchar(250),
    work_phone_ext varchar(250),
    home_phone varchar(250),
    mobile_phone varchar(250),
    fax_phone varchar(250),
    home_email varchar(250),
    work_email varchar(250),
    birth_date integer,
    sin varchar(250),
    note text,
    created_date integer  DEFAULT NULL,
    created_by integer  DEFAULT NULL,
    updated_date integer  DEFAULT NULL,
    updated_by integer  DEFAULT NULL,
    deleted_date integer  DEFAULT NULL,
    deleted_by integer  DEFAULT NULL,
    deleted smallint(6) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE ethnic_group (
    id integer AUTO_INCREMENT NOT NULL,
    company_id integer DEFAULT 0 NOT NULL,
    name varchar(250),
    created_date integer  DEFAULT NULL,
    created_by integer  DEFAULT NULL,
    updated_date integer  DEFAULT NULL,
    updated_by integer  DEFAULT NULL,
    deleted_date integer  DEFAULT NULL,
    deleted_by integer  DEFAULT NULL,
    deleted smallint(6) NOT NULL DEFAULT 0,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE company_setting (
    id integer AUTO_INCREMENT NOT NULL,
    company_id integer NOT NULL,
    type_id integer NOT NULL,
    name varchar(250) NOT NULL,
    value varchar(250),
    created_date integer,
    created_by integer,
    updated_date integer,
    updated_by integer,
    deleted_date integer,
    deleted_by integer,
    deleted smallint(6) DEFAULT 0 NOT NULL,
    PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE user_setting (
    id integer AUTO_INCREMENT NOT NULL,
    user_id integer NOT NULL,
    type_id integer NOT NULL,
    name varchar(250) NOT NULL,
    value varchar(250),
    created_date integer,
    created_by integer,
    updated_date integer,
    updated_by integer,
    deleted_date integer,
    deleted_by integer,
    deleted smallint(6) DEFAULT 0 NOT NULL,
    PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE INDEX qualification_company_id ON qualification(company_id);
CREATE INDEX qualification_type_id ON qualification(type_id);
CREATE INDEX qualification_group_company_id ON qualification_group(company_id);
CREATE INDEX qualification_group_tree_left_id_right_id ON qualification_group_tree(left_id, right_id);
CREATE INDEX qualification_group_tree_id_object_id ON qualification_group_tree(tree_id, object_id);
CREATE INDEX qualification_group_tree_id_parent_id ON qualification_group_tree(tree_id, parent_id);

CREATE INDEX user_education_user_id ON user_education(user_id);
CREATE INDEX user_license_user_id ON user_license(user_id);
CREATE INDEX user_skill_user_id ON user_skill(user_id);
CREATE INDEX user_language_user_id ON user_language(user_id);
CREATE INDEX user_membership_user_id ON user_membership(user_id);

CREATE INDEX user_education_qualification_id ON user_education(qualification_id);
CREATE INDEX user_license_qualification_id ON user_license(qualification_id);
CREATE INDEX user_skill_qualification_id ON user_skill(qualification_id);
CREATE INDEX user_language_qualification_id ON user_language(qualification_id);
CREATE INDEX user_membership_qualification_id ON user_membership(qualification_id);

CREATE INDEX kpi_company_id ON kpi(company_id);
CREATE INDEX kpi_group_company_id ON kpi_group(company_id);
CREATE INDEX kpi_group_tree_left_id_right_id ON kpi_group_tree(left_id, right_id);
CREATE INDEX kpi_group_tree_tree_id_object_id ON kpi_group_tree(tree_id, object_id);
CREATE INDEX kpi_group_tree_tree_id_parent_id ON kpi_group_tree(tree_id, parent_id);

CREATE INDEX user_review_kpi_id ON user_review(kpi_id);
CREATE INDEX user_review_control_id ON user_review(user_review_control_id);
CREATE INDEX user_review_control_user_id ON user_review_control(user_id);
CREATE INDEX user_contact_user_id ON user_contact(user_id);

CREATE INDEX ethnic_group_company_id ON ethnic_group(company_id);

CREATE INDEX company_setting_company_id ON company_setting(company_id);
CREATE INDEX user_setting_user_id ON user_setting(user_id);

ALTER TABLE pay_stub_entry ADD COLUMN user_expense_id integer DEFAULT 0;
ALTER TABLE company ADD COLUMN migrate_url varchar(250);
ALTER TABLE users ADD COLUMN ethnic_group_id integer DEFAULT 0;
ALTER TABLE users ADD COLUMN default_job_id integer DEFAULT 0;
ALTER TABLE users ADD COLUMN default_job_item_id integer DEFAULT 0;
ALTER TABLE currency ADD COLUMN round_decimal_places smallint NOT NULL DEFAULT 2;
