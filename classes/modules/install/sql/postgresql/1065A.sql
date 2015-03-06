ALTER TABLE user_date_total RENAME TO user_date_total_old;
ALTER SEQUENCE "user_date_total_id_seq" RENAME TO "user_date_total_old_id_seq";
DROP INDEX "user_date_total_id";
DROP INDEX "user_date_total_user_date_id";

CREATE TABLE user_date_total (
	id serial NOT NULL,
	user_id integer NOT NULL,
	pay_period_id integer NOT NULL,
	date_stamp date NOT NULL,
	object_type_id smallint NOT NULL,
	src_object_id integer DEFAULT 0 NOT NULL,
	pay_code_id integer DEFAULT 0 NOT NULL,
	punch_control_id integer DEFAULT 0 NOT NULL,
	branch_id integer DEFAULT 0 NOT NULL,
	department_id integer DEFAULT 0 NOT NULL,
	job_id integer DEFAULT 0 NOT NULL,
	job_item_id integer DEFAULT 0 NOT NULL,
	quantity numeric DEFAULT 0 NOT NULL,
	bad_quantity numeric DEFAULT 0 NOT NULL,
	start_type_id smallint,
	start_time_stamp timestamp with time zone,
	end_type_id smallint,
	end_time_stamp timestamp with time zone,
	total_time integer DEFAULT 0 NOT NULL,
	actual_total_time integer DEFAULT 0,
	currency_id integer DEFAULT 0 NOT NULL,
	currency_rate numeric(18,10) DEFAULT 1 NOT NULL,
	base_hourly_rate numeric(18,4) DEFAULT 0,
	hourly_rate numeric(18,4) DEFAULT 0,
	total_time_amount numeric(18,4) DEFAULT 0,
	hourly_rate_with_burden numeric(18,4) DEFAULT 0,
	total_time_amount_with_burden numeric(18,4) DEFAULT 0,
	override smallint DEFAULT 0 NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL,
	note character varying
);

-- Divide by 3600.00 so its a float rather than integer;
INSERT INTO user_date_total (id,user_id,pay_period_id,date_stamp,object_type_id,src_object_id,pay_code_id,punch_control_id,branch_id,department_id,job_id,job_item_id,quantity,bad_quantity,start_type_id,start_time_stamp,end_type_id,end_time_stamp,total_time,actual_total_time,currency_id,currency_rate,base_hourly_rate,hourly_rate,total_time_amount,hourly_rate_with_burden,total_time_amount_with_burden,override,created_date,created_by,updated_date,updated_by,deleted_date,deleted_by,deleted)
(
	SELECT
			tmp3.id,tmp3.user_id,tmp3.pay_period_id,tmp3.date_stamp,object_type_id,tmp3.src_object_id,tmp3.pay_code_id,tmp3.punch_control_id,tmp3.branch_id,tmp3.department_id,tmp3.job_id,tmp3.job_item_id,tmp3.quantity,tmp3.bad_quantity,tmp3.start_type_id,tmp3.start_time_stamp,tmp3.end_type_id,tmp3.end_time_stamp,tmp3.total_time,tmp3.actual_total_time,tmp3.currency_id,tmp3.currency_rate,
			tmp3.hourly_rate as base_hourly_rate,
			tmp3.hourly_rate as hourly_rate,
			tmp3.total_time_amount,
			tmp3.hourly_rate_with_burden,
			(tmp3.hourly_rate_with_burden * (tmp3.total_time / 3600.00) ) as total_time_amount_with_burden,
			tmp3.override,tmp3.created_date,tmp3.created_by,tmp3.updated_date,tmp3.updated_by,tmp3.deleted_date,tmp3.deleted_by,tmp3.deleted
	FROM (
		SELECT
			tmp2.id,tmp2.user_id,tmp2.pay_period_id,tmp2.date_stamp,object_type_id,tmp2.src_object_id,tmp2.pay_code_id,tmp2.punch_control_id,tmp2.branch_id,tmp2.department_id,tmp2.job_id,tmp2.job_item_id,tmp2.quantity,tmp2.bad_quantity,tmp2.start_type_id,tmp2.start_time_stamp,tmp2.end_type_id,tmp2.end_time_stamp,tmp2.total_time,tmp2.actual_total_time,tmp2.currency_id,tmp2.currency_rate,
			tmp2.hourly_rate as base_hourly_rate,
			tmp2.hourly_rate as hourly_rate,
			(tmp2.hourly_rate * (tmp2.total_time / 3600.00) ) as total_time_amount,
			CASE WHEN labor_burden_percent != 0 THEN (tmp2.hourly_rate * ( ( labor_burden_percent / 100.00 ) + 1.00 ) ) ELSE tmp2.hourly_rate END as hourly_rate_with_burden,
			0 as total_time_amount_with_burden,
			tmp2.override,tmp2.created_date,tmp2.created_by,tmp2.updated_date,tmp2.updated_by,tmp2.deleted_date,tmp2.deleted_by,tmp2.deleted
		FROM (
			SELECT
				tmp1.id,tmp1.user_id,tmp1.pay_period_id,tmp1.date_stamp,object_type_id,tmp1.src_object_id,
				CASE WHEN tmp1.pay_code_id > 0 THEN tmp1.pay_code_id ELSE 0 END as pay_code_id,
				tmp1.punch_control_id,tmp1.branch_id,tmp1.department_id,tmp1.job_id,tmp1.job_item_id,tmp1.quantity,tmp1.bad_quantity,tmp1.start_type_id,tmp1.start_time_stamp,tmp1.end_type_id,tmp1.end_time_stamp,tmp1.total_time,tmp1.actual_total_time,tmp1.currency_id,tmp1.currency_rate,
				pc.type_id as pay_code_type_id,
				tmp1.base_hourly_rate,
				CASE WHEN pc.type_id = 30 THEN uw.hourly_rate * -1
				ELSE
					CASE
						WHEN pfp.pay_type_id = 10 THEN pfp.rate * CASE WHEN uw.hourly_rate IS NULL THEN 0 ELSE uw.hourly_rate END
						WHEN pfp.pay_type_id = 30 THEN pfp.rate - CASE WHEN uw.hourly_rate IS NULL THEN 0 ELSE uw.hourly_rate END
						WHEN pfp.pay_type_id = 32 THEN pfp.rate
						WHEN pfp.pay_type_id = 40 THEN CASE WHEN uw.hourly_rate IS NULL OR pfp.rate > uw.hourly_rate THEN pfp.rate - CASE WHEN uw.hourly_rate IS NULL THEN 0 ELSE uw.hourly_rate END ELSE 0 END
						WHEN pfp.pay_type_id = 42 THEN CASE WHEN uw.hourly_rate IS NULL OR pfp.rate > uw.hourly_rate THEN pfp.rate ELSE uw.hourly_rate END
					ELSE 0 END
				END as hourly_rate,
				tmp1.total_time_amount,
				tmp1.hourly_rate_with_burden,
				uw.labor_burden_percent as labor_burden_percent,
				tmp1.total_time_amount_with_burden,
				tmp1.override,tmp1.created_date,tmp1.created_by,tmp1.updated_date,tmp1.updated_by,tmp1.deleted_date,tmp1.deleted_by,tmp1.deleted
			FROM (
				SELECT a.id,b.user_id,b.pay_period_id,b.date_stamp,
					CASE WHEN a.status_id = 10 AND a.type_id = 10 THEN 5 WHEN a.status_id = 20 AND a.type_id = 10 THEN 10 WHEN a.status_id = 30 AND a.type_id = 10 THEN 50 ELSE a.type_id END as object_type_id,
					CASE WHEN a.status_id = 30 AND a.type_id = 10 THEN a.absence_policy_id WHEN a.status_id = 10 AND a.type_id = 20 THEN rtp.id ELSE 0 END as src_object_id,
					CASE WHEN a.status_id = 10 AND a.type_id = 20 THEN rtp.pay_code_id WHEN a.status_id = 10 AND a.type_id = 30 THEN otp.pay_code_id WHEN a.status_id = 10 AND a.type_id = 40 THEN pp.pay_code_id WHEN a.status_id = 10 AND a.type_id = 100 THEN mp.pay_code_id WHEN a.status_id = 10 AND a.type_id = 110 THEN bp.pay_code_id WHEN a.status_id = 30 AND a.type_id = 10 THEN ap.pay_code_id ELSE 0 END as pay_code_id,
					a.punch_control_id,a.branch_id,a.department_id,a.job_id,a.job_item_id,a.quantity,a.bad_quantity,
					CASE WHEN a.punch_control_id > 0 THEN p1.type_id ELSE NULL END as start_type_id,
					CASE WHEN a.punch_control_id > 0 THEN p1.time_stamp ELSE NULL END as start_time_stamp,
					CASE WHEN a.punch_control_id > 0 THEN p2.type_id ELSE NULL END as end_type_id,
					CASE WHEN a.punch_control_id > 0 THEN p2.time_stamp ELSE NULL END as end_time_stamp,
					CASE WHEN total_time is NOT NULL THEN total_time ELSE 0 END as total_time,
					CASE WHEN actual_total_time is NOT NULL THEN actual_total_time ELSE 0 END as actual_total_time,
					c.currency_id,
					CASE WHEN cr.conversion_rate IS NOT NULL THEN ( 1.00 / cr.conversion_rate ) ELSE 1 END as currency_rate,
					0 as base_hourly_rate,
					0 as hourly_rate,
					0 as total_time_amount,
					0 as hourly_rate_with_burden,
					0 as total_time_amount_with_burden,
					a.override,a.created_date,a.created_by,a.updated_date,a.updated_by,a.deleted_date,a.deleted_by,a.deleted
				FROM user_date_total_old as a
					LEFT JOIN user_date as b ON a.user_date_id = b.id
					LEFT JOIN users as c ON b.user_id = c.id
					LEFT JOIN punch as p1 ON ( p1.punch_control_id = a.punch_control_id AND p1.status_id = 10 AND p1.deleted = 0 )
					LEFT JOIN punch as p2 ON ( p2.punch_control_id = a.punch_control_id AND p2.status_id = 20 AND p2.deleted = 0 )
					LEFT JOIN regular_time_policy as rtp ON rtp.id = ( SELECT id FROM regular_time_policy WHERE company_id = c.company_id ORDER BY calculation_order DESC LIMIT 1 )
					LEFT JOIN over_time_policy as otp ON ( otp.id = a.over_time_policy_id AND otp.deleted = 0 )
					LEFT JOIN premium_policy as pp ON ( pp.id = a.premium_policy_id AND pp.deleted = 0 )
					LEFT JOIN meal_policy as mp ON ( mp.id = a.meal_policy_id AND mp.deleted = 0 )
					LEFT JOIN break_policy as bp ON ( bp.id = a.break_policy_id AND bp.deleted = 0 )
					LEFT JOIN absence_policy as ap ON ( ap.id = a.absence_policy_id AND ap.deleted = 0 )
					LEFT JOIN currency_rate as cr ON ( cr.currency_id = c.currency_id AND cr.date_stamp = b.date_stamp )
				WHERE ( ( a.total_time != 0 OR a.override = 1 ) AND a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )
			) as tmp1
			LEFT JOIN pay_code as pc ON ( pc.id = tmp1.pay_code_id AND pc.deleted = 0 )
			LEFT JOIN pay_formula_policy as pfp ON ( pfp.id = pc.pay_formula_policy_id AND pfp.deleted = 0 )
			LEFT JOIN (
				SELECT ud.user_id,ud.date_stamp,uwb.wage_group_id,uwb.hourly_rate,uwb.labor_burden_percent
				FROM user_date as ud
				CROSS JOIN ( SELECT distinct user_id,wage_group_id from user_wage WHERE deleted = 0 ) as uwb_b
				LEFT JOIN user_wage as uwb ON uwb.id = (
					SELECT id
					FROM user_wage
					WHERE user_id = ud.user_id
					AND wage_group_id = uwb_b.wage_group_id
					AND effective_date <= ud.date_stamp
					AND deleted = 0
					ORDER BY effective_date DESC LIMIT 1 )
				WHERE ud.user_id = uwb_b.user_id AND ud.deleted = 0 AND uwb.deleted = 0
			) as uw ON ( tmp1.user_id = uw.user_id AND tmp1.date_stamp = uw.date_stamp AND pfp.wage_group_id = uw.wage_group_id )
		) as tmp2
	) as tmp3
);

SELECT setval('user_date_total_id_seq', max(id) ) from user_date_total;

--Add index so below queries based on object_type_id will be faster;
CREATE INDEX "user_date_total_user_id_user_date" ON "user_date_total" USING btree (user_id, date_stamp);
CREATE INDEX "user_date_total_object_type_id" ON "user_date_total" USING btree (object_type_id);

-- WE NEED TO CALCULATE LUNCH/BREAK (object_type_id=101,111) time taken as well;
INSERT INTO user_date_total (user_id,pay_period_id,date_stamp,object_type_id,src_object_id,pay_code_id,punch_control_id,branch_id,department_id,job_id,job_item_id,quantity,bad_quantity,start_type_id,start_time_stamp,end_type_id,end_time_stamp,total_time,actual_total_time,currency_id,currency_rate,hourly_rate,base_hourly_rate,hourly_rate_with_burden,total_time_amount,total_time_amount_with_burden,override,created_date,created_by,updated_date,updated_by,deleted_date,deleted_by,deleted)
(
	SELECT user_id,pay_period_id,date_stamp,object_type_id,src_object_id,pay_code_id,punch_control_id,branch_id,department_id,job_id,job_item_id,quantity,bad_quantity,start_type_id,start_time_stamp,end_type_id,end_time_stamp,
	CASE WHEN ( EXTRACT( EPOCH FROM end_time_stamp ) - EXTRACT( EPOCH FROM start_time_stamp) ) > 0 THEN ( EXTRACT( EPOCH FROM end_time_stamp ) - EXTRACT( EPOCH FROM start_time_stamp) ) ELSE 0 END as total_time,
	CASE WHEN ( EXTRACT( EPOCH FROM end_time_stamp ) - EXTRACT( EPOCH FROM start_time_stamp) ) > 0 THEN ( EXTRACT( EPOCH FROM end_time_stamp ) - EXTRACT( EPOCH FROM start_time_stamp) ) ELSE 0 END as actual_total_time,
	currency_id,currency_rate,hourly_rate,base_hourly_rate,hourly_rate_with_burden,total_time_amount,total_time_amount_with_burden,override,created_date,created_by,updated_date,updated_by,deleted_date,deleted_by,deleted
	FROM (
		SELECT a.user_id,a.pay_period_id,a.date_stamp,
		101 as object_type_id,
		0 as src_object_id,
		0 as pay_code_id,
		0 as punch_control_id,
		0 as branch_id,
		0 as department_id,
		0 as job_id,
		0 as job_item_id,
		0 as quantity,
		0 as bad_quantity,
		20 as start_type_id,
		end_time_stamp as start_time_stamp,
		( select z.start_time_stamp FROM user_date_total as z WHERE a.user_id = z.user_id AND a.date_stamp = z.date_stamp AND a.end_time_stamp <= z.start_time_stamp AND z.start_type_id = 20 ORDER BY z.start_time_stamp ASC LIMIT 1 ) as end_time_stamp,
		20 as end_type_id,
		currency_id,
		currency_rate,
		0 as hourly_rate,
		0 as base_hourly_rate,
		0 as hourly_rate_with_burden,
		0 as total_time_amount,
		0 as total_time_amount_with_burden,
		0 as override,
		created_date,created_by,updated_date,updated_by,deleted_date,deleted_by,deleted
		FROM user_date_total as a
		WHERE a.end_type_id = 20
	) as tmp1
);
INSERT INTO user_date_total (user_id,pay_period_id,date_stamp,object_type_id,src_object_id,pay_code_id,punch_control_id,branch_id,department_id,job_id,job_item_id,quantity,bad_quantity,start_type_id,start_time_stamp,end_type_id,end_time_stamp,total_time,actual_total_time,currency_id,currency_rate,hourly_rate,base_hourly_rate,hourly_rate_with_burden,total_time_amount,total_time_amount_with_burden,override,created_date,created_by,updated_date,updated_by,deleted_date,deleted_by,deleted)
(
	SELECT user_id,pay_period_id,date_stamp,object_type_id,src_object_id,pay_code_id,punch_control_id,branch_id,department_id,job_id,job_item_id,quantity,bad_quantity,start_type_id,start_time_stamp,end_type_id,end_time_stamp,
	CASE WHEN ( EXTRACT( EPOCH FROM end_time_stamp ) - EXTRACT( EPOCH FROM start_time_stamp) ) > 0 THEN ( EXTRACT( EPOCH FROM end_time_stamp ) - EXTRACT( EPOCH FROM start_time_stamp) ) ELSE 0 END as total_time,
	CASE WHEN ( EXTRACT( EPOCH FROM end_time_stamp ) - EXTRACT( EPOCH FROM start_time_stamp) ) > 0 THEN ( EXTRACT( EPOCH FROM end_time_stamp ) - EXTRACT( EPOCH FROM start_time_stamp) ) ELSE 0 END as actual_total_time,
	currency_id,currency_rate,hourly_rate,base_hourly_rate,hourly_rate_with_burden,total_time_amount,total_time_amount_with_burden,override,created_date,created_by,updated_date,updated_by,deleted_date,deleted_by,deleted
	FROM (
		SELECT a.user_id,a.pay_period_id,a.date_stamp,
		111 as object_type_id,
		0 as src_object_id,
		0 as pay_code_id,
		0 as punch_control_id,
		0 as branch_id,
		0 as department_id,
		0 as job_id,
		0 as job_item_id,
		0 as quantity,
		0 as bad_quantity,
		30 as start_type_id,
		end_time_stamp as start_time_stamp,
		( select z.start_time_stamp FROM user_date_total as z WHERE a.user_id = z.user_id AND a.date_stamp = z.date_stamp AND a.end_time_stamp <= z.start_time_stamp AND z.start_type_id = 30 ORDER BY z.start_time_stamp ASC LIMIT 1 ) as end_time_stamp,
		30 as end_type_id,
		currency_id,
		currency_rate,
		0 as hourly_rate,
		0 as base_hourly_rate,
		0 as hourly_rate_with_burden,
		0 as total_time_amount,
		0 as total_time_amount_with_burden,
		0 as override,
		created_date,created_by,updated_date,updated_by,deleted_date,deleted_by,deleted
		FROM user_date_total as a
		WHERE a.end_type_id = 30
	) as tmp1
);

-- WE NEED TO DUPLICATE EVERY object_type_id = 50 (absence) and copy it to object_type_id = 25;
INSERT INTO user_date_total (user_id,pay_period_id,date_stamp,object_type_id,src_object_id,pay_code_id,punch_control_id,branch_id,department_id,job_id,job_item_id,quantity,bad_quantity,start_type_id,start_time_stamp,end_type_id,end_time_stamp,total_time,actual_total_time,currency_id,currency_rate,hourly_rate,base_hourly_rate,hourly_rate_with_burden,total_time_amount,total_time_amount_with_burden,override,created_date,created_by,updated_date,updated_by,deleted_date,deleted_by,deleted)
(
	SELECT user_id,pay_period_id,date_stamp,25,src_object_id,pay_code_id,punch_control_id,branch_id,department_id,job_id,job_item_id,quantity,bad_quantity,start_type_id,start_time_stamp,end_type_id,end_time_stamp,total_time,actual_total_time,currency_id,currency_rate,hourly_rate,base_hourly_rate,hourly_rate_with_burden,total_time_amount,total_time_amount_with_burden,0,created_date,created_by,updated_date,updated_by,deleted_date,deleted_by,deleted
	FROM user_date_total
	WHERE object_type_id = 50
);

-- CLEAR OUT DOLLARS FOR ABSENCE (TAKEN) TIME, SO ITS NOT DOUBLED UP WITH ABSENCE (CALCULATED) TIME;
UPDATE user_date_total set hourly_rate = 0, base_hourly_rate = 0, hourly_rate_with_burden = 0, total_time_amount = 0, total_time_amount_with_burden = 0 WHERE object_type_id = 50;

-- Finish creating remaining indexes;
CREATE UNIQUE INDEX "user_date_total_id" ON "user_date_total" USING btree (id);
CREATE INDEX "user_date_total_pay_code_id" ON "user_date_total" USING btree (pay_code_id);
CREATE INDEX "user_date_total_pay_period_id" ON "user_date_total" USING btree (pay_period_id);
CREATE INDEX "user_date_total_branch_id" ON "user_date_total" USING btree (branch_id);
CREATE INDEX "user_date_total_department_id" ON "user_date_total" USING btree (department_id);
CREATE INDEX "user_date_total_job_id" ON "user_date_total" USING btree (job_id);
CREATE INDEX "user_date_total_job_item_id" ON "user_date_total" USING btree (job_item_id);
ALTER TABLE "user_date_total" CLUSTER ON "user_date_total_user_id_user_date";


ALTER TABLE punch_control RENAME TO punch_control_old;
ALTER SEQUENCE "punch_control_id_seq" RENAME TO "punch_control_old_id_seq";
DROP INDEX "punch_control_id";
DROP INDEX "punch_control_user_date_id";

CREATE TABLE punch_control (
	id serial NOT NULL,
	user_id integer NOT NULL,
	pay_period_id integer NOT NULL,
	date_stamp date NOT NULL,
	branch_id integer DEFAULT 0 NOT NULL,
	department_id integer DEFAULT 0 NOT NULL,
	job_id integer DEFAULT 0 NOT NULL,
	job_item_id integer DEFAULT 0 NOT NULL,
	quantity numeric DEFAULT 0 NOT NULL,
	bad_quantity numeric DEFAULT 0 NOT NULL,
	total_time integer DEFAULT 0 NOT NULL,
	actual_total_time integer DEFAULT 0 NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL,
	other_id1 character varying,
	other_id2 character varying,
	other_id3 character varying,
	other_id4 character varying,
	other_id5 character varying,
	note character varying
);
INSERT INTO punch_control (id,user_id,pay_period_id,date_stamp,branch_id,department_id,job_id,job_item_id,quantity,bad_quantity,total_time,actual_total_time,created_date,created_by,updated_date,updated_by,deleted_date,deleted_by,deleted,other_id1,other_id2,other_id3,other_id4,other_id5,note)
(
	SELECT a.id,b.user_id,b.pay_period_id,b.date_stamp,a.branch_id,a.department_id,a.job_id,a.job_item_id,a.quantity,a.bad_quantity,a.total_time,a.actual_total_time,a.created_date,a.created_by,a.updated_date,a.updated_by,a.deleted_date,a.deleted_by,a.deleted,a.other_id1,a.other_id2,a.other_id3,a.other_id4,a.other_id5,a.note
	FROM punch_control_old as a
	LEFT JOIN user_date as b ON a.user_date_id = b.id
	LEFT JOIN users as c ON b.user_id = c.id
	WHERE ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )
);
SELECT setval('punch_control_id_seq', max(id) ) from punch_control;

CREATE UNIQUE INDEX "punch_control_id" ON "punch_control" USING btree (id);
CREATE INDEX "punch_control_user_id_user_date" ON "punch_control" USING btree (user_id, date_stamp);
CREATE INDEX "punch_control_pay_period_id" ON "punch_control" USING btree (pay_period_id);
CREATE INDEX "punch_control_branch_id" ON "punch_control" USING btree (branch_id);
CREATE INDEX "punch_control_department_id" ON "punch_control" USING btree (department_id);
CREATE INDEX "punch_control_job_id" ON "punch_control" USING btree (job_id);
CREATE INDEX "punch_control_job_item_id" ON "punch_control" USING btree (job_item_id);
ALTER TABLE "punch_control" CLUSTER ON "punch_control_user_id_user_date";



ALTER TABLE schedule RENAME TO schedule_old;
ALTER SEQUENCE "schedule_id_seq" RENAME TO "schedule_old_id_seq";
DROP INDEX "schedule_id";
DROP INDEX "schedule_company_id";
DROP INDEX "schedule_recurring_schedule_template_control_id";
DROP INDEX "schedule_start_time_end_time";
DROP INDEX "schedule_user_date_id";

CREATE TABLE schedule (
	id serial NOT NULL,
	company_id integer NOT NULL,
	user_id integer NOT NULL,
	pay_period_id integer NOT NULL,
	date_stamp date NOT NULL,
	status_id smallint DEFAULT 10 NOT NULL,
	start_time timestamp with time zone NOT NULL,
	end_time timestamp with time zone NOT NULL,
	schedule_policy_id integer DEFAULT 0 NOT NULL,
	absence_policy_id integer DEFAULT 0 NOT NULL,
	branch_id integer DEFAULT 0 NOT NULL,
	department_id integer DEFAULT 0 NOT NULL,
	job_id integer DEFAULT 0 NOT NULL,
	job_item_id integer DEFAULT 0 NOT NULL,
	total_time integer DEFAULT 0 NOT NULL,
	replaced_id integer DEFAULT 0 NOT NULL,
	recurring_schedule_template_control_id integer DEFAULT 0 NOT NULL,
	auto_fill smallint DEFAULT 0 NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL,
	other_id1 character varying,
	other_id2 character varying,
	other_id3 character varying,
	other_id4 character varying,
	other_id5 character varying,
	note character varying
);
INSERT INTO schedule (id,company_id,user_id,pay_period_id,date_stamp,status_id,start_time,end_time,schedule_policy_id,absence_policy_id,branch_id,department_id,job_id,job_item_id,total_time,replaced_id,recurring_schedule_template_control_id,created_date,created_by,updated_date,updated_by,deleted_date,deleted_by,deleted,note)
(
	SELECT a.id,a.company_id,b.user_id,b.pay_period_id,b.date_stamp,a.status_id,a.start_time,a.end_time,
	CASE WHEN a.schedule_policy_id IS NULL THEN 0 ELSE a.schedule_policy_id END,
	CASE WHEN a.absence_policy_id IS NULL THEN 0 ELSE a.absence_policy_id END,
	CASE WHEN a.branch_id IS NULL THEN 0 ELSE a.branch_id END,
	CASE WHEN a.department_id IS NULL THEN 0 ELSE a.department_id END,
	CASE WHEN a.job_id IS NULL THEN 0 ELSE a.job_id END,
	CASE WHEN a.job_item_id IS NULL THEN 0 ELSE a.job_item_id END,
	a.total_time,a.replaced_id,a.recurring_schedule_template_control_id,a.created_date,a.created_by,a.updated_date,a.updated_by,a.deleted_date,a.deleted_by,a.deleted,a.note
	FROM schedule_old as a
	LEFT JOIN user_date as b ON a.user_date_id = b.id
	LEFT JOIN users as c ON b.user_id = c.id
	WHERE ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )
);
SELECT setval('schedule_id_seq', max(id) ) from schedule;

CREATE UNIQUE INDEX "schedule_id" ON "schedule" USING btree (id);
CREATE INDEX "schedule_user_id_user_date" ON "schedule" USING btree (user_id, date_stamp);
CREATE INDEX "schedule_pay_period_id" ON "schedule" USING btree (pay_period_id);
CREATE INDEX "schedule_company_id" ON "schedule" USING btree (company_id);
CREATE INDEX "schedule_branch_id" ON "schedule" USING btree (branch_id);
CREATE INDEX "schedule_department_id" ON "schedule" USING btree (department_id);
CREATE INDEX "schedule_job_id" ON "schedule" USING btree (job_id);
CREATE INDEX "schedule_job_item_id" ON "schedule" USING btree (job_item_id);
CREATE INDEX "schedule_company_recurring_schedule_template_control_id" ON "schedule" USING btree (recurring_schedule_template_control_id);
ALTER TABLE "schedule" CLUSTER ON "schedule_user_id_user_date";


ALTER TABLE exception RENAME TO exception_old;
ALTER SEQUENCE "exception_id_seq" RENAME TO "exception_old_id_seq";
DROP INDEX "exception_id";
DROP INDEX "exception_user_date_id";

CREATE TABLE exception (
	id serial NOT NULL,
	user_id integer NOT NULL,
	pay_period_id integer NOT NULL,
	date_stamp date NOT NULL,
	exception_policy_id integer NOT NULL,
	punch_id integer DEFAULT 0 NOT NULL,
	punch_control_id integer DEFAULT 0 NOT NULL,
	type_id smallint NOT NULL,
	enable_demerit smallint DEFAULT 1 NOT NULL,
	authorized smallint DEFAULT 0 NOT NULL,
	authorization_level smallint DEFAULT 99 NOT NULL,
	acknowledged_type_id smallint DEFAULT 0 NOT NULL,
	acknowledged_reason_id integer DEFAULT 0 NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL,
	note character varying
);
INSERT INTO exception (id,user_id,pay_period_id,date_stamp,exception_policy_id,punch_id,punch_control_id,type_id,enable_demerit,authorized,created_date,created_by,updated_date,updated_by,deleted_date,deleted_by,deleted)
(
	SELECT a.id,b.user_id,b.pay_period_id,b.date_stamp,a.exception_policy_id,
	CASE WHEN a.punch_id > 0 THEN a.punch_id ELSE 0 END,
	CASE WHEN a.punch_control_id > 0 THEN a.punch_control_id ELSE 0 END,
	a.type_id,a.enable_demerit,a.authorized,a.created_date,a.created_by,a.updated_date,a.updated_by,a.deleted_date,a.deleted_by,a.deleted
	FROM exception_old as a
	LEFT JOIN user_date as b ON a.user_date_id = b.id
	LEFT JOIN users as c ON b.user_id = c.id
	WHERE ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )
);
SELECT setval('exception_id_seq', max(id) ) from exception;

CREATE UNIQUE INDEX "exception_id" ON "exception" USING btree (id);
CREATE INDEX "exception_user_id_user_date" ON "exception" USING btree (user_id, date_stamp);
CREATE INDEX "exception_pay_period_id" ON "exception" USING btree (pay_period_id);
ALTER TABLE "exception" CLUSTER ON "exception_user_id_user_date";



ALTER TABLE request RENAME TO request_old;
ALTER SEQUENCE "request_id_seq" RENAME TO "request_old_id_seq";
DROP INDEX "request_id";
DROP INDEX "request_status_id_authorized";
DROP INDEX "request_user_date_id";

CREATE TABLE request (
	id serial NOT NULL,
	user_id integer NOT NULL,
	pay_period_id integer NOT NULL,
	date_stamp date NOT NULL,
	type_id smallint NOT NULL,
	status_id smallint NOT NULL,
	authorized smallint DEFAULT 0 NOT NULL,
	authorization_level smallint DEFAULT 99 NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL
);
INSERT INTO request (id,user_id,pay_period_id,date_stamp,type_id,status_id,authorized,authorization_level,created_date,created_by,updated_date,updated_by,deleted_date,deleted_by,deleted)
(
	SELECT a.id,b.user_id,b.pay_period_id,b.date_stamp,a.type_id,a.status_id,a.authorized,a.authorization_level,a.created_date,a.created_by,a.updated_date,a.updated_by,a.deleted_date,a.deleted_by,a.deleted
	FROM request_old as a
	LEFT JOIN user_date as b ON a.user_date_id = b.id
	LEFT JOIN users as c ON b.user_id = c.id
	WHERE ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )	
);
SELECT setval('request_id_seq', max(id) ) from request;

CREATE UNIQUE INDEX "request_id" ON "request" USING btree (id);
CREATE INDEX "request_user_id_user_date" ON "request" USING btree (user_id, date_stamp);
CREATE INDEX "request_pay_period_id" ON "request" USING btree (pay_period_id);
CREATE INDEX "request_type_id" ON "request" USING btree (type_id);
CREATE INDEX "request_status_id" ON "request" USING btree (status_id);
ALTER TABLE "request" CLUSTER ON "request_user_id_user_date";