ALTER INDEX user_review_control_id rename to user_review_user_review_control_id;

CREATE UNIQUE INDEX qualification_id ON qualification(id);
CREATE UNIQUE INDEX user_education_id ON user_education(id);
CREATE UNIQUE INDEX user_license_id ON user_license(id);
CREATE UNIQUE INDEX user_skill_id ON user_skill(id);
CREATE UNIQUE INDEX user_language_id ON user_language(id);
CREATE UNIQUE INDEX user_membership_id ON user_membership(id);
CREATE UNIQUE INDEX qualification_group_id ON qualification_group(id);
CREATE UNIQUE INDEX kpi_id ON kpi(id);
CREATE UNIQUE INDEX user_review_control_id ON user_review_control(id);
CREATE UNIQUE INDEX user_review_id ON user_review(id);
CREATE UNIQUE INDEX kpi_group_id ON kpi_group(id);
CREATE UNIQUE INDEX user_contact_id ON user_contact(id);
CREATE UNIQUE INDEX ethnic_group_id ON ethnic_group(id);
CREATE UNIQUE INDEX company_setting_id ON company_setting(id);
CREATE UNIQUE INDEX user_setting_id ON user_setting(id);

CREATE UNIQUE INDEX station_branch_id ON station_branch(id);
CREATE UNIQUE INDEX station_department_id ON station_department(id);
CREATE UNIQUE INDEX station_user_group_id ON station_user_group(id);
CREATE UNIQUE INDEX station_include_user_id ON station_include_user(id);
CREATE UNIQUE INDEX station_exclude_user_id ON station_exclude_user(id);

CREATE UNIQUE INDEX hierarchy_level_id ON hierarchy_level(id);
CREATE UNIQUE INDEX hierarchy_user_id ON hierarchy_user(id);


ALTER TABLE company_deduction ALTER COLUMN company_value1 TYPE text;
ALTER TABLE company_deduction ALTER COLUMN company_value2 TYPE text;