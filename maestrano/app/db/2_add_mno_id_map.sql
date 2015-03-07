CREATE TABLE IF NOT EXISTS mno_id_map (
  mno_entity_guid varchar(255) NOT NULL,
  mno_entity_name varchar(255) NOT NULL,
  app_entity_id integer NOT NULL,
  app_entity_name varchar(255) NOT NULL,
  db_timestamp timestamp NOT NULL DEFAULT LOCALTIMESTAMP(0),
  deleted_flag smallint NOT NULL DEFAULT 0
);
ALTER TABLE public.mno_id_map OWNER TO timetrex;
