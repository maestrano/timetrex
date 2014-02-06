alter table accrual_policy_milestone ENGINE = InnoDB;
alter table pay_stub change start_date start_date timestamp NULL default NULL;
alter table pay_stub change end_date end_date timestamp NULL default NULL;
alter table pay_stub change transaction_date transaction_date timestamp NULL default NULL;

alter table user_preference add column language varchar(5);
alter table user_default add column language varchar(5);

CREATE TABLE currency (
    id serial NOT NULL,
	company_id integer NOT NULL,
	status_id integer NOT NULL,
    name varchar(250) NOT NULL,
	iso_code varchar(5) NOT NULL,
	conversion_rate numeric(18,10),
	auto_update smallint,
	actual_rate numeric(18,10),
	actual_rate_updated_date integer,
	rate_modify_percent numeric(18,10),
	is_base smallint DEFAULT 0 NOT NULL,
	is_default smallint DEFAULT 0 NOT NULL,	
    created_date integer,
    created_by integer,
    updated_date integer,
    updated_by integer,
    deleted_date integer,
    deleted_by integer,
    deleted smallint DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

alter table users add column currency_id integer;
alter table user_default add column currency_id integer;
alter table pay_stub add column currency_id integer;
alter table pay_stub add column currency_rate numeric(18,10);

CREATE TABLE policy_group_accrual_policy (
    id integer AUTO_INCREMENT NOT NULL,
    policy_group_id integer DEFAULT 0 NOT NULL,
    accrual_policy_id integer DEFAULT 0 NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB;
