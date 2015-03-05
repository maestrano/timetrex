ALTER TABLE recurring_schedule_template add column open_shift_multiplier integer default 1;

ALTER TABLE schedule rename to schedule_tmp;
DROP INDEX schedule_id ON schedule_tmp;
DROP INDEX schedule_recurring_schedule_template_control_id ON schedule_tmp;
DROP INDEX schedule_start_time ON schedule_tmp;
DROP INDEX schedule_user_date_id ON schedule_tmp;

CREATE TABLE schedule (
	id integer NOT NULL,
	company_id integer DEFAULT 0 NOT NULL,
	user_date_id integer NOT NULL,
	status_id integer NOT NULL,
	replaced_id integer DEFAULT 0 NOT NULL,
	recurring_schedule_template_control_id integer DEFAULT 0 NOT NULL,
	start_time timestamp NOT NULL,
	end_time timestamp NOT NULL,
	schedule_policy_id integer,
	absence_policy_id integer,
	branch_id integer,
	department_id integer,
	job_id integer,
	job_item_id integer,
	total_time integer,
	note text,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);
INSERT INTO schedule (id,company_id,user_date_id,status_id,start_time,end_time,schedule_policy_id,absence_policy_id,branch_id,department_id,job_id,job_item_id,total_time,note,created_date,created_by,updated_date,updated_by,deleted_date,deleted_by,deleted) ( select a.id,CASE WHEN c.company_id is NOT NULL THEN c.company_id ELSE 0 END,a.user_date_id,a.status_id,a.start_time,a.end_time,a.schedule_policy_id,a.absence_policy_id,a.branch_id,a.department_id,a.job_id,a.job_item_id,a.total_time,a.note,a.created_date,a.created_by,a.updated_date,a.updated_by,a.deleted_date,a.deleted_by,a.deleted	FROM schedule_tmp as a LEFT JOIN user_date as b ON a.user_date_id = b.id LEFT JOIN users as c ON b.user_id = c.id ORDER BY a.user_date_id );
DROP TABLE schedule_tmp;

CREATE UNIQUE INDEX schedule_id ON schedule(id);
CREATE INDEX schedule_recurring_schedule_template_control_id ON schedule(recurring_schedule_template_control_id);
CREATE INDEX schedule_start_time_end_time ON schedule(start_time,end_time);
CREATE INDEX schedule_user_date_id ON schedule(user_date_id);
CREATE INDEX schedule_company_id ON schedule(company_id);

UPDATE schedule_id_seq set ID = ( select max(id) from schedule )+1;

DELETE FROM user_wage where user_id = 0;

UPDATE company_generic_map_id_seq set ID = ( select max(id) from company_generic_map )+1;
