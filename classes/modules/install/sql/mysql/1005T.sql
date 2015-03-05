CREATE TABLE income_tax_rate_cr (
	id integer AUTO_INCREMENT NOT NULL,
	country varchar(250),
	state varchar(250),
	district varchar(250),
	effective_date integer NOT NULL,
	status integer NOT NULL,
	income numeric(20,4) NOT NULL,
	rate numeric(20,4) NOT NULL,
	constant numeric(20,4) DEFAULT 0,
	PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE UNIQUE INDEX income_tax_rate_cr_id_uniq ON income_tax_rate_cr(id);

INSERT INTO income_tax_rate_cr VALUES (DEFAULT, 'CR', NULL, NULL, 1159678800, 10, 5616000, 0, 0);
INSERT INTO income_tax_rate_cr VALUES (DEFAULT, 'CR', NULL, NULL, 1159678800, 10, 8424000, 10, 0);
INSERT INTO income_tax_rate_cr VALUES (DEFAULT, 'CR', NULL, NULL, 1159678800, 10, 8424000, 15, 0);

INSERT INTO income_tax_rate_cr VALUES (DEFAULT, 'CR', NULL, NULL, 1191214800, 10, 6096000, 0, 0);
INSERT INTO income_tax_rate_cr VALUES (DEFAULT, 'CR', NULL, NULL, 1191214800, 10, 9144000, 10, 0);
INSERT INTO income_tax_rate_cr VALUES (DEFAULT, 'CR', NULL, NULL, 1191214800, 10, 9144000, 15, 0);
