ALTER TABLE company ADD COLUMN is_setup_complete SMALLINT NOT NULL DEFAULT 0;

ALTER TABLE schedule ADD COLUMN recurring_schedule_template_control_id INTEGER NOT NULL DEFAULT 0;
ALTER TABLE schedule ADD COLUMN replaced_id INTEGER NOT NULL DEFAULT 0;
ALTER TABLE schedule ADD COLUMN note text DEFAULT NULL;

ALTER TABLE recurring_schedule_template ADD COLUMN status_id INTEGER NOT NULL DEFAULT 10;
ALTER TABLE recurring_schedule_template ADD COLUMN absence_policy_id INTEGER NOT NULL DEFAULT 0;

ALTER TABLE user_preference ADD COLUMN enable_always_blank_timesheet_rows SMALLINT NOT NULL DEFAULT 1;
ALTER TABLE user_preference ADD COLUMN enable_auto_context_menu SMALLINT NOT NULL DEFAULT 1;
ALTER TABLE user_preference ADD COLUMN enable_report_open_new_window SMALLINT NOT NULL DEFAULT 1;
ALTER TABLE user_preference ADD COLUMN user_full_name_format SMALLINT NOT NULL DEFAULT 10;
ALTER TABLE user_preference ADD COLUMN shortcut_key_sequence varchar(250) DEFAULT 'CTRL+ALT';

ALTER TABLE user_title ADD COLUMN other_id1 varchar(250);
ALTER TABLE user_title ADD COLUMN other_id2 varchar(250);
ALTER TABLE user_title ADD COLUMN other_id3 varchar(250);
ALTER TABLE user_title ADD COLUMN other_id4 varchar(250);
ALTER TABLE user_title ADD COLUMN other_id5 varchar(250);

CREATE INDEX exception_policy_active_type_id on exception_policy(active,type_id);
CREATE INDEX schedule_recurring_schedule_template_control_id on schedule(recurring_schedule_template_control_id);

UPDATE user_default set deleted = 1 where company_id in ( select company_id from ( select company_id from user_default where deleted = 0 group by company_id having count(*) > 1 ) as tmp1 ) AND id not in ( select id from ( select max(id) as id from user_default where deleted = 0 group by company_id having count(*) > 1 ) as tmp2 );
