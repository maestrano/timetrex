CREATE TABLE income_tax_rate_cr (
	id serial NOT NULL,
	country character varying,
	state character varying,
	district character varying,
	effective_date integer NOT NULL,
	status integer NOT NULL,
	income numeric NOT NULL,
	rate numeric NOT NULL,
	constant numeric DEFAULT 0
);

CREATE UNIQUE INDEX income_tax_rate_cr_id_uniq ON income_tax_rate_cr USING btree (id);

INSERT INTO income_tax_rate_cr VALUES (DEFAULT, 'CR', NULL, NULL, 1159678800, 10, 5616000, 0, 0);
INSERT INTO income_tax_rate_cr VALUES (DEFAULT, 'CR', NULL, NULL, 1159678800, 10, 8424000, 10, 0);
INSERT INTO income_tax_rate_cr VALUES (DEFAULT, 'CR', NULL, NULL, 1159678800, 10, 8424000, 15, 0);

INSERT INTO income_tax_rate_cr VALUES (DEFAULT, 'CR', NULL, NULL, 1191214800, 10, 6096000, 0, 0);
INSERT INTO income_tax_rate_cr VALUES (DEFAULT, 'CR', NULL, NULL, 1191214800, 10, 9144000, 10, 0);
INSERT INTO income_tax_rate_cr VALUES (DEFAULT, 'CR', NULL, NULL, 1191214800, 10, 9144000, 15, 0);
