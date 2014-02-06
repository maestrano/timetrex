alter table pay_period change start_date start_date timestamp NULL default NULL;
alter table pay_period change end_date end_date timestamp NULL default NULL;
alter table pay_period change transaction_date transaction_date timestamp NULL default NULL;
alter table pay_period change advance_end_date advance_end_date timestamp NULL default NULL;
alter table pay_period change advance_transaction_date advance_transaction_date timestamp NULL default NULL;

update pay_period set advance_end_date = NULL;
update pay_period set advance_transaction_date = NULL;
