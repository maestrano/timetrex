ALTER TABLE company_deduction ADD COLUMN company_value3 text;
ALTER TABLE company_deduction ADD COLUMN company_value4 text;
ALTER TABLE company_deduction ADD COLUMN company_value5 text;
ALTER TABLE company_deduction ADD COLUMN company_value6 text;
ALTER TABLE company_deduction ADD COLUMN company_value7 text;
ALTER TABLE company_deduction ADD COLUMN company_value8 text;
ALTER TABLE company_deduction ADD COLUMN company_value9 text;
ALTER TABLE company_deduction ADD COLUMN company_value10 text;

ALTER TABLE company_deduction ADD COLUMN apply_frequency_id smallint DEFAULT 10;
ALTER TABLE company_deduction ADD COLUMN apply_frequency_month smallint;
ALTER TABLE company_deduction ADD COLUMN apply_frequency_day_of_month smallint;
ALTER TABLE company_deduction ADD COLUMN apply_frequency_day_of_week smallint;
ALTER TABLE company_deduction ADD COLUMN apply_frequency_quarter_month smallint;

ALTER TABLE company_deduction ADD COLUMN pay_stub_entry_description character varying;

ALTER TABLE pay_stub_amendment ADD COLUMN private_description character varying;

ALTER TABLE pay_stub_entry_account ADD COLUMN accrual_type_id smallint DEFAULT 10;

ALTER TABLE pay_stub ADD COLUMN confirm_number varchar(100);

CREATE INDEX station_company_id_status_id_type_id ON station(company_id,status_id,type_id);
